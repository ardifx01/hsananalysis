@extends('layouts.app')

@section('title', 'Rekap Perbandingan Belanja Rekening per SKPD')
@section('page-title', 'Perbandingan Belanja Rekening per SKPD')

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
        font-size: 10px;
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

    .nama-rekening {
        max-width: 150px; /* Atur ukuran kolom Nama Rekening */
        white-space: normal; /* Wrap text */
        word-wrap: break-word; /* Wrap text */
    }
</style>

<!-- DataTables CSS -->
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/jquery.dataTables.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.3.6/css/buttons.dataTables.min.css">

<div class="card" data-aos="fade-up" data-aos-delay="800">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h4>Perbandingan Belanja Rekening per SKPD</h4>
        <form method="GET" action="{{ route('compareDataOpdRek') }}">
            <select name="kode_skpd" onchange="this.form.submit()">
                <option value="">Pilih SKPD</option>
                @foreach($skpds as $skpd)
                    <option value="{{ $skpd->kode_skpd }}" {{ $kodeSkpd == $skpd->kode_skpd ? 'selected' : '' }}>
                        {{ $skpd->nama_skpd }}
                    </option>
                @endforeach
            </select>
        </form>
        <div class="dt-buttons"></div>
    </div>

    <div class="card-body">
        @if($rekap->isNotEmpty())
        <div class="table-container">
            <table id="rekapTable" class="table table-striped table-bordered">
                <thead>
                    <tr>
                        <th  style="text-align: center;">No</th> <!-- Kolom Nomor Urut -->
                        <th  style="text-align: center;">Kode Rekening</th>
                        <th class="nama-rekening" style="text-align: center;">Nama Rekening</th>
                        @foreach($rekap->first() as $data)
                            <th style="text-align: center;">{{ optional($tahapans->find($data->tahapan_id))->name }}<br><span style="font-size:10px">{{ $data->tanggal_upload }}<br>{{ $data->jam_upload }}</span></th>
                        @endforeach
                        <th  style="text-align: center;">Selisih</th>
                        <th  style="text-align: center;">Persentase Selisih</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($rekap as $kode_rekening => $data)
                    <tr>
                        <td>{{ $loop->iteration }}</td> <!-- Nomor Urut Manual -->
                        <td>{{ $kode_rekening }}</td>
                        <td class="nama-rekening">{{ $data->first()->nama_rekening }}</td>
                        @foreach($data as $item)
                            <td class="total-pagu-{{ $item->tahapan_id }}-{{ $item->tanggal_upload }}-{{ $item->jam_upload }}">
                                {{ number_format($item->total_pagu, 2, ',', '.') }}
                            </td>
                        @endforeach
                        <td class="selisih-pagu">{{ number_format($selisihPagu[$kode_rekening], 2, ',', '.') }}</td>
                        <td class="persentase-selisih-pagu">{{ number_format($persentaseSelisihPagu[$kode_rekening], 2, ',', '.') }}%</td>
                    </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr class="table-dark">
                        <th colspan="3" class="text-end">Total:</th>
                        @foreach($rekap->first() as $data)
                            <th id="totalPagu{{ $data->tahapan_id }}-{{ $data->tanggal_upload }}-{{ $data->jam_upload }}">
                                {{ number_format($totalPagu[$data->tahapan_id . '_' . str_replace('-', '_', $data->tanggal_upload) . '_' . str_replace(':', '_', $data->jam_upload)], 2, ',', '.') }}
                            </th>
                        @endforeach
                        <th id="totalSelisihPagu">{{ number_format($totalSelisihPagu, 2, ',', '.') }}</th>
                        <th id="totalPersentaseSelisihPagu">{{ number_format($totalPersentaseSelisihPagu, 2, ',', '.') }}%</th>
                    </tr>
                </tfoot>
            </table>
        </div>
        @else
        <p>Silakan pilih SKPD untuk melihat data.</p>
        @endif
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
            paging: false,
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
                        var totalPagu = 0;
                        $('#rekapTable tbody tr').each(function() {
                            var totalPaguRow = parseFloat($(this).find('.total-pagu').text().replace(/\./g, '').replace(',', '.')) || 0;
                            totalPagu += totalPaguRow;
                        });
                        doc.content[1].table.body.push([
                            { text: "Total", bold: true, alignment: "right", colSpan: 3 }, {}, {}, 
                            { text: totalPagu.toLocaleString('id-ID'), bold: true }
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
            drawCallback: function(settings) {
                table.column(0).nodes().each(function(cell, i) { cell.innerHTML = i + 1; }); // Tambahkan nomor urut
            }
        });
    });
</script>

@endsection