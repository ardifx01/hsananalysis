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
<link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/jquery.dataTables.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.2.2/css/buttons.dataTables.min.css">

<div class="card" data-aos="fade-up" data-aos-delay="800">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h4>Perbandingan Belanja Rekening per SKPD</h4>
        <div>
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
        </div>
    </div>

    <div class="card-body">
        @if($rekap->isNotEmpty())
        <div class="table-container">
            <div class="mb-2">
                Hitung Selisih:
                <select id="minuend-col"></select>
                &minus;
                <select id="subtrahend-col"></select>
                <button id="hitung-selisih" class="btn btn-sm btn-primary ms-2">Hitung</button>
            </div>
            <table id="rekapTable" class="table table-striped table-bordered">
                <thead>
                    <tr>
                        <th  style="text-align: center;">No</th> <!-- Kolom Nomor Urut -->
                        <th  style="text-align: center;">Kode Rekening</th>
                        <th class="nama-rekening" style="text-align: center;">Nama Rekening</th>
                        @php
                            $headerCount = 0; // To track column count
                        @endphp
                        @foreach($rekap->first() as $data)
                            <th style="text-align: center;">{{ optional($tahapans->find($data->tahapan_id))->name }}<br><span style="font-size:10px">{{ $data->tanggal_upload }}<br>{{ $data->jam_upload }}</span></th>
                            @php $headerCount++; @endphp
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
                        @php
                            $itemCount = 0; // To ensure column count matches header
                        @endphp
                        @foreach($data as $item)
                            <td class="total-pagu-{{ $item->tahapan_id }}-{{ $item->tanggal_upload }}-{{ $item->jam_upload }}">
                                {{ number_format($item->total_pagu, 2, ',', '.') }}
                            </td>
                            @php $itemCount++; @endphp
                        @endforeach
                        
                        @php
                            // Add empty cells if needed to match header count
                            while($itemCount < $headerCount) {
                                echo '<td></td>';
                                $itemCount++;
                            }
                        @endphp
                        
                        <td class="selisih-pagu">{{ number_format($selisihPagu[$kode_rekening], 2, ',', '.') }}</td>
                        <td class="persentase-selisih-pagu">{{ number_format($persentaseSelisihPagu[$kode_rekening], 2, ',', '.') }}%</td>
                    </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr class="table-dark">
                        <th colspan="3" class="text-end">Total:</th>
                        @php 
                            $footerCount = 0;
                        @endphp
                        @foreach($rekap->first() as $data)
                            <th id="totalPagu{{ $data->tahapan_id }}-{{ $data->tanggal_upload }}-{{ $data->jam_upload }}">
                                {{ number_format($totalPagu[$data->tahapan_id . '_' . str_replace('-', '_', $data->tanggal_upload) . '_' . str_replace(':', '_', $data->jam_upload)], 2, ',', '.') }}
                            </th>
                            @php $footerCount++; @endphp
                        @endforeach
                        
                        @php
                            // Add empty cells if needed to match header count
                            while($footerCount < $headerCount) {
                                echo '<th></th>';
                                $footerCount++;
                            }
                        @endphp
                        
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
<script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.2.2/js/dataTables.buttons.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.1.3/jszip.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/pdfmake.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/vfs_fonts.js"></script>
<script src="https://cdn.datatables.net/buttons/2.2.2/js/buttons.html5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.2.2/js/buttons.print.min.js"></script>

<script>
    $(document).ready(function() {
        // Basic initialization first, then add features
        var dataTable = $('#rekapTable').DataTable({
            paging: false,
            searching: true,
            info: true,
            order: [[1, 'asc']],
            columnDefs: [
                { orderable: false, targets: 0 }
            ],
            dom: 'Bfrtip',
            buttons: [
                {
                    extend: 'excel',
                    text: '📊 Export Excel',
                    title: 'Perbandingan Belanja Rekening per SKPD',
                    exportOptions: {
                        columns: ':visible',
                        format: {
                            body: function(data, row, column, node) {
                                if (column >= 3) {
                                    let clean = ('' + data)
                                        .replace(/\s/g, '')        // Hilangkan semua whitespace
                                        .replace(/[^0-9,.-]/g, '') // Hanya angka, minus, koma, titik
                                        .replace(/\./g, '')        // Hilangkan titik ribuan
                                        .replace(/,/g, '.');        // Ganti koma desimal jadi titik
                                    // Pastikan hasilnya valid number
                                    return clean !== '' && !isNaN(clean) ? clean : '';
                                }
                                return typeof data === 'string' ? data.replace(/<[^>]*>?/gm, '') : data;
                            }
                        }
                    }
                },
                {
                    extend: 'pdf',
                    text: '📄 Export PDF',
                    title: 'Perbandingan Belanja Rekening per SKPD',
                    orientation: 'landscape',
                    pageSize: 'A4',
                    exportOptions: {
                        columns: ':visible'
                    }
                }
            ]
        });
        
        // Update row numbers manually
        dataTable.on('order.dt search.dt', function() {
            dataTable.column(0, {search:'applied', order:'applied'}).nodes().each(function(cell, i) {
                cell.innerHTML = i + 1;
            });
        }).draw();

        // --- Fitur Selisih Dinamis ---
        // Ambil header kolom dinamis (kecuali No, Kode Rekening, Nama Rekening, Selisih, Persentase)
        let headerCells = $('#rekapTable thead tr').eq(0).find('th');
        let options = '';
        headerCells.each(function(i) {
            // Hanya kolom data (bukan No, Kode Rekening, Nama Rekening, Selisih, Persentase)
            if (i > 2 && i < headerCells.length - 2) {
                options += `<option value="${i}">${$(this).text().split('\n')[0]}</option>`;
            }
        });
        $('#minuend-col, #subtrahend-col').html(options);
        $('#minuend-col').val(headerCells.length - 4); // Default: kolom terakhir sebelum Selisih
        $('#subtrahend-col').val(3); // Default: kolom pertama data

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
        updateSelisih();
        // --- END Fitur Selisih Dinamis ---
    });
</script>

@endsection
