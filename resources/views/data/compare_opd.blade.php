@extends('layouts.app')

@section('title', 'Rekap Perbandingan Belanja OPD')
@section('page-title', 'Perbandingan Belanja OPD')

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
        font-size: 14px;
    }

    th {
        background-color: #0056b3!important;
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

    .dt-buttons .btn {
        margin-right: 5px;
    }
</style>

<!-- DataTables CSS -->
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/jquery.dataTables.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.3.6/css/buttons.dataTables.min.css">

<div class="card" data-aos="fade-up" data-aos-delay="800">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h4>Perbandingan Belanja OPD</h4>
        <div class="dt-buttons"></div>
    </div>

    <div class="card-body">
        <div class="table-container">
            <table id="rekapTable" class="table table-striped table-bordered">
                <thead>
                    <tr>
                        <th>No</th> <!-- Kolom Nomor Urut -->
                        <th>Kode OPD</th>
                        <th>Nama OPD</th>
                        <th>Pagu Murni</th>
                        <th>Pagu Perubahan</th>
                        <th>Selisih</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($rekap as $index => $data)
                    <tr>
                        <td>{{ $index + 1 }}</td> <!-- Nomor Urut Manual -->
                        <td>{{ $data['kode_skpd'] }}</td>
                        <td>{{ Str::limit($data['nama_skpd'], 50) }}</td>
                        <td class="pagu-original">{{ number_format($data['pagu_original'], 2, ',', '.') }}</td>
                        <td class="pagu-revisi">{{ number_format($data['pagu_revisi'], 2, ',', '.') }}</td>
                        <td class="pagu-selisih">{{ number_format($data['selisih'], 2, ',', '.') }}</td>
                    </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr class="table-dark">
                        <th colspan="3" class="text-end">Total:</th>
                        <th id="totalOriginal">0</th>
                        <th id="totalRevisi">0</th>
                        <th id="totalSelisih">0</th>
                    </tr>
                </tfoot>
            </table>
        </div>

        <div class="total-container">
            Total Pagu Original: <strong id="totalOriginalFooter">0</strong> |
            Total Pagu Revisi: <strong id="totalRevisiFooter">0</strong> |
            Total Selisih: <strong id="totalSelisihFooter">0</strong>
        </div>

        <a href="{{ url('/import') }}" class="btn btn-secondary mt-3">Kembali ke Upload</a>
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
            paging: false, // Pagination Dinonaktifkan
            searching: true,
            ordering: true,
            info: true,
            columnDefs: [
                { targets: 0, searchable: false, orderable: false }, // Kolom Nomor Urut
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
                        modifier: { search: 'applied' },
                        format: {
                            body: function(data, row, column, node) {
                                if (column === 0) return row + 1; // Nomor urut saat export
                                return data.replace(/\./g, '').replace(',', '.');
                            }
                        }
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
                    },
                    customize: function(doc) {
                        doc.content[1].table.body.forEach(function(row, index) {
                            if (index > 0) {
                                row[0].text = index; // Tambahkan nomor urut di PDF
                            }
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
                info: "Menampilkan _TOTAL_ data",
                paginate: {
                    first: "Awal",
                    last: "Akhir",
                    next: "Berikutnya",
                    previous: "Sebelumnya"
                }
            },
            initComplete: function() {
                updateTotal();
            },
            drawCallback: function() {
                updateTotal();
                table.column(0).nodes().each(function(cell, i) { cell.innerHTML = i + 1; }); // Tambahkan nomor urut
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

            $('#totalOriginal, #totalOriginalFooter').text(totalOriginal.toLocaleString('id-ID'));
            $('#totalRevisi, #totalRevisiFooter').text(totalRevisi.toLocaleString('id-ID'));
            $('#totalSelisih, #totalSelisihFooter').text(totalSelisih.toLocaleString('id-ID'));
        }
    });
</script>

@endsection
