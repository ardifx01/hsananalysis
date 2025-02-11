@extends('layouts.app')

@section('content')
<style>
    .table-container {
        width: 100%;
        overflow-x: auto;
    }

    table {
        width: 100%;
        border-collapse: collapse;
        background-color: #fff;
        box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.1);
        border-radius: 5px;
        overflow: hidden;
    }

    th, td {
        padding: 10px;
        text-align: left;
        border-bottom: 1px solid #ddd;
        font-size: 12px;
        white-space: normal;
        word-wrap: break-word;
    }

    th {
        background-color: #007bff;
        color: white;
        font-weight: bold;
    }

    tr:hover {
        background-color: #f1f1f1;
    }

    .total-container {
        margin-top: 20px;
        font-size: 16px;
        font-weight: bold;
        text-align: right;
    }
</style>

<div class="card" data-aos="fade-up" data-aos-delay="800">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h4>Rekap Perbandingan Data Anggaran</h4>
    </div>

    <div class="card-body">
        <form id="filterForm" class="mb-3">
            <div class="row">
                <div class="col-md-4">
                    <label for="kode_opd">Kode OPD / Nama OPD</label>
                    <input type="text" class="form-control" id="kode_opd" placeholder="Masukkan Kode atau Nama OPD">
                </div>
                <div class="col-md-4">
                    <label for="kode_rekening">Kode Rekening</label>
                    <input type="text" class="form-control" id="kode_rekening" placeholder="Masukkan Kode Rekening">
                </div>
                <div class="col-md-4 d-flex align-items-end">
                    <button type="button" class="btn btn-primary me-2" id="btnFilter">Cari</button>
                    <button type="button" class="btn btn-secondary" id="btnReset">Reset</button>
                </div>
            </div>
        </form>

        <div class="table-container">
            <table id="rekapTable">
                <thead>
                    <tr>
                        <th>Kode OPD</th>
                        <th>Nama OPD</th>
                        <th>Kode Rekening</th>
                        <th>Nama Rekening</th>
                        <th>Pagu Original</th>
                        <th>Pagu Revisi</th>
                        <th>Selisih</th>
                        <th>Persentase Selisih (%)</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($rekap as $data)
                    <tr>
                        <td>{{ $data['kode_opd'] }}</td>
                        <td>{{ $data['nama_opd'] }}</td>
                        <td>{{ $data['kode_rekening'] }}</td>
                        <td>{{ $data['nama_rekening'] }}</td>
                        <td class="pagu-original">{{ number_format($data['pagu_original'], 2, ',', '.') }}</td>
                        <td class="pagu-revisi">{{ number_format($data['pagu_revisi'], 2, ',', '.') }}</td>
                        <td class="pagu-selisih">{{ number_format($data['selisih'], 2, ',', '.') }}</td>
                        <td class="persentase-selisih">0%</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
    $(document).ready(function() {
        var table = $('#rekapTable').DataTable({
            "paging": true,
            "searching": true,
            "ordering": true,
            "info": true,
            "lengthMenu": [50, 100, 500, 1000],
            "language": {
                "search": "Cari Data:",
                "lengthMenu": "Tampilkan _MENU_ data per halaman",
                "info": "Menampilkan _START_ sampai _END_ dari _TOTAL_ data",
            }
        });

        $('#btnFilter').click(function() {
            var kode_opd = $('#kode_opd').val().toLowerCase();
            var kode_rekening = $('#kode_rekening').val().toLowerCase();
            
            table.columns().every(function() {
                var column = this;
                if (column.index() === 0 || column.index() === 1) {
                    column.search(kode_opd, true, false);
                } else if (column.index() === 2) {
                    column.search(kode_rekening, true, false);
                }
            });

            table.draw();
        });

        $('#btnReset').click(function() {
            $('#kode_opd').val('');
            $('#kode_rekening').val('');
            table.search('').columns().search('').draw();
        });
    });
</script>

@endsection
