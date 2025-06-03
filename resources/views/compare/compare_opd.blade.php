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
    .nama-opd {
        max-width: 150px; /* Atur ukuran kolom Nama Rekening */
        white-space: normal; /* Wrap text */
        word-wrap: break-word; /* Wrap text */
    }
</style>

<div class="card" data-aos="fade-up" data-aos-delay="800">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h4>Perbandingan Belanja OPD</h4>
        <div class="dt-buttons"></div>
    </div>

    <div class="card-body">
        <div class="mb-2">
            Hitung Selisih:
            <select id="minuend-col"></select>
            &minus;
            <select id="subtrahend-col"></select>
            <button id="hitung-selisih" class="btn btn-sm btn-primary ms-2">Hitung</button>
        </div>
        <div class="table-container">
            <table id="rekapTable" class="table table-striped table-bordered">
                <thead>
                    <tr>
                        <th  style="text-align: center;">No</th> <!-- Kolom Nomor Urut -->
                        <th  style="text-align: center;">Kode OPD</th>
                        <th class="nama-opd" style="text-align: center;">Nama OPD</th>
                        @foreach($rekap->first() as $data)
                            <th style="text-align: center;">
                                {{ $tahapans->where('id', $data->tahapan_id)->first()->name ?? '' }}<br>
                                <span style="font-size:10px">{{ $data->tanggal_upload }}<br>{{ $data->jam_upload }}</span>
                            </th>
                        @endforeach
                        <th  style="text-align: center;">Selisih</th>
                        <th  style="text-align: center;">Persentase</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($rekap as $kode_skpd => $data)
                    <tr>
                        <td>{{ $loop->iteration }}</td> <!-- Nomor Urut Manual -->
                        <td>{{ $kode_skpd }}</td>
                        <td class="nama-opd">{{ $data->first()->nama_skpd }}</td>
                        @foreach($data as $item)
                            <td class="total-pagu-{{ $item->tahapan_id }}-{{ $item->tanggal_upload }}-{{ $item->jam_upload }}">
                                {{ number_format($item->total_pagu, 2, ',', '.') }}
                            </td>
                        @endforeach
                        <td class="selisih-pagu">{{ number_format($selisihPagu[$kode_skpd], 2, ',', '.') }}</td>
                        <td class="persentase-selisih-pagu">{{ number_format($persentaseSelisihPagu[$kode_skpd], 2, ',', '.') }}%</td>
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
        var tableElement = $('#rekapTable');
        
        if (tableElement.length && tableElement.find('tbody tr').length > 0) {
            console.log("Tabel ditemukan, DataTables akan diinisialisasi.");
            
            function fillDropdownSelisih() {
                let headerCells = $('#rekapTable thead tr').eq(0).find('th');
                let options = '';
                headerCells.each(function(i) {
                    // Hanya kolom data (bukan No, Kode OPD, Nama OPD, Selisih, Persentase)
                    if (i > 2 && i < headerCells.length - 2) {
                        let label = this.childNodes[0] ? this.childNodes[0].textContent.trim() : $(this).text().trim();
                        options += `<option value="${i}">${label}</option>`;
                    }
                });
                $('#minuend-col, #subtrahend-col').html(options);
                $('#minuend-col').val(headerCells.length - 4); // Default: kolom terakhir sebelum Selisih
                $('#subtrahend-col').val(3); // Default: kolom pertama data
            }

            var table = tableElement.DataTable({
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
                                    // Kolom Selisih (dinamis)
                                    if ($(node).hasClass('selisih-pagu')) {
                                        return $(node).text().replace(/\./g, '').replace(',', '.');
                                    }
                                    // Kolom Persentase Selisih (dinamis)
                                    if ($(node).hasClass('persentase-selisih-pagu')) {
                                        return $(node).text().replace('%','').replace(',','.');
                                    }
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
                initComplete: function() {
                    fillDropdownSelisih();
                    updateSelisih();
                },
                drawCallback: function(settings) {
                    var api = this.api();
                    api.column(0).nodes().each(function(cell, i) {
                        cell.innerHTML = i + 1;
                    });
                    fillDropdownSelisih();
                }
            });

            function updateSelisih() {
                let minCol = parseInt($('#minuend-col').val());
                let subCol = parseInt($('#subtrahend-col').val());
                let totalSelisih = 0;
                let totalPersen = 0, count = 0;
                $('#rekapTable tbody tr').each(function() {
                    let minVal = parseFloat($(this).find('td').eq(minCol).text().replace(/\./g, '').replace(',', '.')) || 0;
                    let subVal = parseFloat($(this).find('td').eq(subCol).text().replace(/\./g, '').replace(',', '.')) || 0;
                    let selisih = minVal - subVal;
                    $(this).find('td.selisih-pagu').text(selisih.toLocaleString('id-ID', {minimumFractionDigits: 2, maximumFractionDigits: 2}));
                    totalSelisih += selisih;
                    // Hitung persentase selisih
                    let persentase = subVal !== 0 ? (selisih / subVal) * 100 : 0;
                    $(this).find('td.persentase-selisih-pagu').text(persentase.toLocaleString('id-ID', {minimumFractionDigits: 2, maximumFractionDigits: 2}) + '%');
                    totalPersen += persentase;
                    count++;
                });
                // Update total di footer
                $('#totalSelisihPagu').text(totalSelisih.toLocaleString('id-ID', {minimumFractionDigits: 2, maximumFractionDigits: 2}));
                // Update total persentase di footer (rata-rata)
                let avgPersen = count > 0 ? totalPersen / count : 0;
                $('#totalPersentaseSelisihPagu').text(avgPersen.toLocaleString('id-ID', {minimumFractionDigits: 2, maximumFractionDigits: 2}) + '%');
            }

            $('#hitung-selisih').on('click', updateSelisih);
            $('#minuend-col, #subtrahend-col').on('change', updateSelisih);
            // --- END Fitur Selisih Dinamis ---
        } else {
            console.warn("Tabel tidak memiliki data, DataTables tidak diinisialisasi.");
        }
    });
</script>

@endsection