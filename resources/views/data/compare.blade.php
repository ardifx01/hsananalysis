@extends('layouts.app')

@section('title', 'Rekap Per OPD Per Rekening')
@section('page-title', 'Rekap Per OPD Per Rekening')

@section('content')

<!-- Styles -->
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
        table-layout: fixed;
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
        background-color: #004080!important;
        color: white;
        font-weight: bold;
        text-align: center;
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

    .dt-buttons {
        margin-bottom: 10px;
    }

    .filter-container {
        margin-bottom: 15px;
    }
</style>

<!-- DataTables CSS -->
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/jquery.dataTables.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.3.6/css/buttons.dataTables.min.css">

<div class="card" data-aos="fade-up" data-aos-delay="800">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h4>Rekap Per OPD Per Rekening</h4>
        <div class="dt-buttons"></div>
    </div>

    <div class="card-body">
        <!-- Filter Berdasarkan OPD -->
<div class="filter-container">
    <label for="filter-opd"><strong>Filter OPD:</strong></label>
    <select id="filter-opd" class="form-select">
        <option value="">Semua OPD</option>
        @foreach(collect($rekap)->pluck('nama_opd')->unique() as $opd)
            <option value="{{ $opd }}">{{ $opd }}</option>
        @endforeach
    </select>
</div>

        <div class="table-container">
            <table id="rekapTable" class="table table-striped table-bordered">
                <thead>
                    <tr>
                        <th>Kode OPD</th>
                        <th>Nama OPD</th>
                        <th>Kode Rekening</th>
                        <th>Nama Rekening</th>
                        <th>Pagu Murni</th>
                        <th>Pagu Perubahan</th>
                        <th>Selisih</th>
                        <th>Persentase Selisih (%)</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($rekap as $data)
                    <tr>
                        <td>{{ $data['kode_opd'] }}</td>
                        <td class="nama-opd">{{ $data['nama_opd'] }}</td>
                        <td>{{ $data['kode_rekening'] }}</td>
                        <td>{{ Str::limit($data['nama_rekening'], 50) }}</td>
                        <td class="pagu-original">{{ number_format($data['pagu_original'], 2, ',', '.') }}</td>
                        <td class="pagu-revisi">{{ number_format($data['pagu_revisi'], 2, ',', '.') }}</td>
                        <td class="pagu-selisih">{{ number_format($data['selisih'], 2, ',', '.') }}</td>
                        <td class="persentase-selisih">0%</td>
                    </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr>
                        <th colspan="4" style="text-align:right">Total:</th>
                        <th id="totalOriginal">0</th>
                        <th id="totalRevisi">0</th>
                        <th id="totalSelisih">0</th>
                        <th id="totalPersentase">0%</th>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>

<!-- jQuery & DataTables -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.3.6/js/dataTables.buttons.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.3.6/js/buttons.html5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.3.6/js/buttons.print.min.js"></script>

<script>
$(document).ready(function() {
    var table = $('#rekapTable').DataTable({
        paging: true,
        searching: true,
        ordering: true,
        info: true,
        lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, "Semua"]],
        order: [[0, 'asc']],
        dom: 'Blfrtip',
        buttons: [
            {
                extend: 'excelHtml5',
                text: '📊 Download Excel',
                className: 'btn btn-success',
                footer: true,
                exportOptions: {
                    columns: ':visible',
                    modifier: { search: 'applied' }
                }
            },
            {
                extend: 'pdfHtml5',
                text: '📄 Download PDF',
                className: 'btn btn-danger',
                orientation: 'landscape',
                pageSize: 'A4',
                footer: true,
                exportOptions: {
                    columns: ':visible',
                    modifier: { search: 'applied' }
                }
            }
        ],
        language: {
            search: "Cari Data:",
            lengthMenu: "Tampilkan _MENU_ data per halaman",
            info: "Menampilkan _START_ sampai _END_ dari _TOTAL_ data",
            paginate: {
                first: "Awal",
                last: "Akhir",
                next: "Berikutnya",
                previous: "Sebelumnya"
            }
        }
    });

    // Filter Berdasarkan OPD
    $('#filter-opd').on('change', function() {
        var selectedOpd = $(this).val();
        if (selectedOpd) {
            table.column(1).search('^' + selectedOpd + '$', true, false).draw();
        } else {
            table.column(1).search('').draw();
        }
    });

    // Hitung Total
    function updateTotal() {
        var totalOriginal = 0, totalRevisi = 0, totalSelisih = 0;

        $('#rekapTable tbody tr:visible').each(function() {
            var paguOriginal = parseFloat($(this).find('.pagu-original').text().replace(/\./g, '').replace(',', '.')) || 0;
            var paguRevisi = parseFloat($(this).find('.pagu-revisi').text().replace(/\./g, '').replace(',', '.')) || 0;
            var paguSelisih = parseFloat($(this).find('.pagu-selisih').text().replace(/\./g, '').replace(',', '.')) || 0;

            totalOriginal += paguOriginal;
            totalRevisi += paguRevisi;
            totalSelisih += paguSelisih;
        });

        $('#totalOriginal').text(totalOriginal.toLocaleString('id-ID'));
        $('#totalRevisi').text(totalRevisi.toLocaleString('id-ID'));
        $('#totalSelisih').text(totalSelisih.toLocaleString('id-ID'));
    }

    table.on('draw', updateTotal);
    updateTotal();
});
</script>

@endsection
