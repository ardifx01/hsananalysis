@extends('layouts.app')

@section('title', 'Set % Rekening Belanja')
@section('page-title', 'Set % Rekening Belanja')

@section('content')

<!-- Import DataTables CSS & JS -->
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css">
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.3.6/js/dataTables.buttons.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.3.6/js/buttons.html5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.3.6/js/buttons.print.min.js"></script>

<style>
    /* Styling Minimalis */
    .table-sm th, .table-sm td {
        padding: 6px 10px; /* Mengurangi padding */
        font-size: 12px; /* Perkecil ukuran font */
    }
    .input-small {
        width: 80px; /* Batasi ukuran input */
        text-align: center;
    }
    .alert {
        margin-bottom: 15px;
    }
    .dt-buttons {
        margin-bottom: 10px; /* Beri jarak antara tombol export dan tabel */
    }
</style>

<div class="container">
    <!-- Notifikasi Sukses -->
    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <!-- Card Container -->
    <div class="card">
        <div class="card-header">
            <h5 class="card-title">Set % Rekening Belanja</h5>
        </div>
        <div class="card-body">
            <!-- Tombol Simpan Perubahan di Atas -->
            <form action="{{ route('set-rek.update') }}" method="POST" id="update-form">
                @csrf
                <button type="submit" class="btn btn-success mb-3">Simpan Perubahan</button>

                <div class="table-responsive">
                    <table id="rekapTable" class="table table-sm table-bordered">
                        <thead class="table-dark text-center">
                            <tr>
                                <th>Kode Rekening</th>
                                <th>Nama Rekening</th>
                                <th>Persentase</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($data as $row)
                                <tr>
                                    <td class="text-center">
                                        <input type="hidden" name="kode_rekening[]" value="{{ $row->kode_rekening }}">
                                        {{ $row->kode_rekening }}
                                    </td>
                                    <td>{{ $row->nama_rekening }}</td>
                                    <td class="text-center">
                                        <input type="number" class="form-control input-small" 
                                               name="persentase_penyesuaian[]" 
                                               value="{{ $row->persentase_penyesuaian ?? 0 }}" 
                                               min="0" max="100" step="0.01">
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
   $(document).ready(function() {
    var table = $('#rekapTable').DataTable({
        paging: true,
        searching: true,
        ordering: true,
        responsive: true,
        dom: 'Bfrtip',
        buttons: [
            {
                extend: 'excelHtml5',
                text: 'Export Excel',
                className: 'btn btn-success',
                exportOptions: {
                    columns: [0, 1, 2], // Sesuaikan dengan indeks kolom
                    modifier: {
                        page: 'all'
                    },
                    format: {
                        body: function(data, row, column, node) {
                            // ** Perbaiki Kode Rekening **
                            if (column === 0) { // Kolom "Kode Rekening"
                                return $(node).clone().children().remove().end().text().trim();
                            }
                            // ** Perbaiki Persentase Penyesuaian **
                            if (column === 2) { // Kolom "Persentase Penyesuaian"
                                return $(node).find('input').val() + " %";
                            }
                            return data;
                        }
                    }
                }
            },
            {
                extend: 'pdfHtml5',
                text: 'Export PDF',
                className: 'btn btn-danger',
                orientation: 'landscape',
                exportOptions: {
                    columns: [0, 1, 2],
                    format: {
                        body: function(data, row, column, node) {
                            if (column === 0) {
                                return $(node).clone().children().remove().end().text().trim();
                            }
                            if (column === 2) {
                                return $(node).find('input').val() + " %";
                            }
                            return data;
                        }
                    }
                }
            },
            {
                extend: 'print',
                text: 'Print',
                className: 'btn btn-primary',
                exportOptions: {
                    columns: [0, 1, 2],
                    format: {
                        body: function(data, row, column, node) {
                            if (column === 0) {
                                return $(node).clone().children().remove().end().text().trim();
                            }
                            if (column === 2) {
                                return $(node).find('input').val() + " %";
                            }
                            return data;
                        }
                    }
                }
            }
        ]
    });
});
</script>

@endsection
