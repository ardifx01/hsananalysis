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
    });
</script>

@endsection
