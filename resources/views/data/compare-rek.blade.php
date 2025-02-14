@extends('layouts.app')

@section('title', 'Rekap Perbandingan Rekening Belanja')
@section('page-title', 'Perbandingan Rekening Belanja')

@section('content')

<style>
    table {
        width: 100%;
        border-collapse: collapse;
        background-color: #fff;
        box-shadow: 0px 2px 10px rgba(0, 0, 0, 0.1);
        border-radius: 5px;
        overflow: hidden;
    }
    th, td {
        padding: 8px 12px;
        text-align: left;
        border-bottom: 1px solid #ddd;
        font-size: 14px;
        white-space: nowrap;
    }
    th {
        background-color: #0056b3!important;
    }
    thead {
        background-color: #0056b3;
        color: white;
        font-weight: bold;
        text-align: center;
        box-shadow: 0px 2px 5px rgba(0, 0, 0, 0.15);
    }
    tbody tr:hover {
        background-color: #f8f9fa;
    }
    tfoot {
        background-color: #004080;
        color: white;
        font-weight: bold;
    }
    .dt-buttons .btn {
        margin-right: 5px;
    }
</style>

<!-- DataTables CSS -->
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/jquery.dataTables.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.3.6/css/buttons.dataTables.min.css">

<div class="card" data-aos="fade-up" data-aos-delay="800">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h4>Perbandingan Rekening Belanja</h4>
        <div class="dt-buttons"></div> <!-- Tombol Export -->
    </div>

    <div class="card-body">
        <div class="table-responsive">
            <table id="rekapTable" class="table table-striped table-bordered">
                <thead>
                    <tr>
                        <th>No</th> <!-- Tambahkan Nomor Urut -->
                        <th>Kode Rekening</th>
                        <th>Nama Rekening</th>
                        <th>Pagu Murni</th>
                        <th>Pagu Perubahan</th>
                        <th>Selisih</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($rekap as $index => $data)
                    <tr>
                        <td class="text-center"></td> <!-- Kolom nomor urut -->
                        <td>{{ $data['kode_rekening'] }}</td>
                        <td>{{ Str::limit($data['nama_rekening'], 50) }}</td>
                        <td class="pagu-original">{{ number_format($data['pagu_original'], 2, ',', '.') }}</td>
                        <td class="pagu-revisi">{{ number_format($data['pagu_revisi'], 2, ',', '.') }}</td>
                        <td class="pagu-selisih">{{ number_format($data['selisih'], 2, ',', '.') }}</td>
                    </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr>
                        <th colspan="3" class="text-end">Total:</th>
                        <th id="totalOriginal">0</th>
                        <th id="totalRevisi">0</th>
                        <th id="totalSelisih">0</th>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>

<!-- jQuery dan DataTables JS -->
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
            columnDefs: [
                { targets: 0, searchable: false, orderable: false }
            ],
            order: [[1, 'asc']],
            dom: 'Blfrtip',
            buttons: [
                {
                    extend: 'excelHtml5',
                    text: '📊 Download Excel',
                    className: 'btn btn-success',
                    footer: true,
                    exportOptions: {
                        columns: ':visible',
                        modifier: { page: 'all' },
                        format: {
                            body: function(data, row, column, node) {
                                return data.replace(/\./g, '').replace(',', '.');
                            },
                            footer: function(data, row, column, node) {
                                return data.replace(/\./g, '').replace(',', '.');
                            }
                        }
                    },
                    customizeData: function(data) {
                        data.body.forEach((row, index) => {
                            row[0] = index + 1; // Menambahkan nomor urut pada setiap baris
                        });
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
                        modifier: { page: 'all' }
                    },
                    customize: function(doc) {
                        doc.content[1].table.body.forEach((row, index) => {
                            if (index > 0) row[0] = index; // Tambahkan nomor urut
                        });
                        doc.content[1].table.body.push([
                            { text: "Total", bold: true, alignment: "right", colSpan: 3 }, {}, {}, 
                            { text: $('#totalOriginal').text(), bold: true },
                            { text: $('#totalRevisi').text(), bold: true },
                            { text: $('#totalSelisih').text(), bold: true }
                        ]);
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
            },
            rowCallback: function(row, data, index) {
                $('td:eq(0)', row).html(index + 1); // Tambahkan nomor urut di UI
            },
            initComplete: function() {
                updateTotal();
            },
            drawCallback: function() {
                updateTotal();
            }
        });

        function updateTotal() {
            var totalOriginal = 0, totalRevisi = 0, totalSelisih = 0;

            $('#rekapTable tbody tr').each(function() {
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
    });
</script>

@endsection
