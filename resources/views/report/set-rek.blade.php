@extends('layouts.app')
@section('title', 'HSAnalysis')
@section('page-title', 'HSAnalysis')
@section('content')

<style>
    /* Styling untuk memperkecil font size dalam tabel */
    table.dataTable tbody tr {
        font-size: 12px;
    }
    table.dataTable thead {
        font-size: 12px;
    }
    table.dataTable tfoot {
        font-size: 12px;
    }

    /* Agar teks yang panjang tidak mempengaruhi layout */
    .truncate {
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        max-width: 200px; /* Batasi lebar kolom */
    }

    /* Tooltip untuk teks yang dipotong */
    .truncate:hover::after {
        content: attr(data-fulltext);
        position: absolute;
        background-color: rgba(0, 0, 0, 0.8);
        color: #fff;
        padding: 5px;
        border-radius: 5px;
        font-size: 12px;
        white-space: normal;
        max-width: 300px;
        z-index: 9999;
    }
</style>

<div class="card" data-aos="fade-up" data-aos-delay="800">
        <div class="flex-wrap card-header d-flex justify-content-between align-items-center">

<!-- Form Filter -->
<form id="filter-form" class="row g-3 mb-4">
    <div class="col-md-2">
        <label for="kode_skpd" class="form-label">Kode SKPD</label>
        <input type="text" name="kode_skpd" id="kode_skpd" class="form-control" placeholder="Masukkan Kode SKPD">
    </div>
    <div class="col-md-2">
        <label for="nama_skpd" class="form-label">Nama SKPD</label>
        <input type="text" name="nama_skpd" id="nama_skpd" class="form-control" placeholder="Masukkan Nama SKPD">
    </div>
    <div class="col-md-2">
        <label for="kode_rekening" class="form-label">Kode Rekening</label>
        <input type="text" name="kode_rekening" id="kode_rekening" class="form-control" placeholder="Masukkan Kode Rekening">
    </div>
    <div class="col-md-2">
        <label for="nama_rekening" class="form-label">Nama Rekening</label>
        <input type="text" name="nama_rekening" id="nama_rekening" class="form-control" placeholder="Masukkan Nama Rekening">
    </div>
    <div class="col-md-2 d-flex align-items-end">
        <button type="submit" class="btn btn-primary w-100">Cari</button>
        <button type="reset" id="reset-filter" class="btn btn-secondary w-100 ms-2">Reset</button>
    </div>
</form>

<!-- Tabel DataTables -->
<div class="table-responsive">
    <table id="reportTable" class="table table-striped table-bordered table-sm">
        <thead class="table-dark">
            <tr>
                <th style="width: 5%;">No</th>
                <th style="width: 10%;">Kode SKPD</th>
                <th style="width: 15%;">Nama SKPD</th>
                <th style="width: 20%;">Nama Rekening</th>
                <th style="width: 15%;">Pagu Original</th>
                <th style="width: 15%;">Pagu Revisi</th>
                <th style="width: 15%;">Selisih</th>
            </tr>
        </thead>
        <tbody></tbody>
        <tfoot>
            <tr>
                <th colspan="4" class="text-end">Total:</th>
                <th id="total-pagu-original">0</th>
                <th id="total-pagu-revisi">0</th>
                <th id="total-pagu-selisih">0</th>
            </tr>
        </tfoot>
    </table>
</div>
</div>
</div>

<!-- jQuery dan DataTables -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap5.min.js"></script>

<script>
    $(document).ready(function() {
        var table = $('#reportTable').DataTable({
            processing: true,
            serverSide: true,
            lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, "Semua"]],
            pageLength: 10,
            ajax: {
                url: "{{ route('report.data') }}",
                data: function (d) {
                    d.kode_skpd = $('#kode_skpd').val();
                    d.nama_skpd = $('#nama_skpd').val();
                    d.nama_rekening = $('#nama_rekening').val();
                }
            },
            columns: [
                { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
                { data: 'kode_skpd', name: 'kode_skpd' },
                { data: 'nama_skpd', name: 'nama_skpd' },
                { 
                    data: 'nama_rekening', 
                    name: 'nama_rekening',
                    render: function(data) {
                        if (data.length > 50) {
                            return `<span class="truncate" data-fulltext="${data}">${data.substring(0, 50)}...</span>`;
                        }
                        return data;
                    }
                },
                { data: 'pagu_original', name: 'pagu_original', render: $.fn.dataTable.render.number(',', '.', 2) },
                { data: 'pagu_revisi', name: 'pagu_revisi', render: $.fn.dataTable.render.number(',', '.', 2) },
                { 
                    data: null, 
                    render: function(data) { 
                        return (data.pagu_revisi - data.pagu_original).toLocaleString(); 
                    }
                }
            ],
            order: [[1, 'asc']],
            drawCallback: function() {
                var totalOriginal = 0, totalRevisi = 0, totalSelisih = 0;

                this.api().rows({ search: 'applied' }).every(function() {
                    var data = this.data();
                    totalOriginal += parseFloat(data.pagu_original);
                    totalRevisi += parseFloat(data.pagu_revisi);
                    totalSelisih += (parseFloat(data.pagu_revisi) - parseFloat(data.pagu_original));
                });

                $('#total-pagu-original').text(totalOriginal.toLocaleString());
                $('#total-pagu-revisi').text(totalRevisi.toLocaleString());
                $('#total-pagu-selisih').text(totalSelisih.toLocaleString());
            },
            dom: 'lBfrtip'
        });

        $('#filter-form').on('submit', function(e) {
            e.preventDefault();
            table.ajax.reload();
        });

        $('#reset-filter').on('click', function(e) {
            e.preventDefault();
            $('#filter-form')[0].reset();
            table.ajax.reload();
        });
    });
</script>

@endsection
