@extends('layouts.app')

@section('title', 'Progress Entry Data')
@section('page-title', 'Progress Entry Data')

@section('content')

<!-- Import DataTables & Buttons -->
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.3.6/css/buttons.bootstrap5.min.css">
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.3.6/js/dataTables.buttons.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.3.6/js/buttons.bootstrap5.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.3.6/js/buttons.html5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.3.6/js/buttons.print.min.js"></script>

<style>
    .table-sm th, .table-sm td { padding: 6px 10px; font-size: 12px; }
    tr.odd td.text-red {
        color: red;
    }
    tr.even td.text-red {
        color: red;
    }
    tr.odd td.text-green {
        color: green;
    }
    tr.even td.text-green {
        color: green;
    }
</style>

<div class="container">
    <div class="card">
        <div class="card-header">
            <h5 class="card-title">Progress Entry Data</h5>
        </div>
        <div class="card-body">
            <!-- Tabel Data -->
            <div class="table-responsive">
                <table id="rekapTable" class="table table-striped table-bordered table-sm">
                    <thead class="table-dark">
                        <tr>
                            <th>No</th>
                            <th>Kode OPD</th>
                            <th>Nama OPD</th>
                            <th>Pagu Murni</th>
                            <th>Persentase Pengurangan</th>
                            <th>Pagu Pengurangan</th>
                            <th>Pagu Setelah Pengurangan</th>
                            <th>Entri SIPD<br>{{ $data->first()->tanggal_upload_terbaru }}</th>
                            <th>Pagu yang sudah dikurangi<br>(Murni-Entri SIPD)</th>
                            <th>Yang belum dikurangi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php
                            $totalPaguOriginal = 0;
                            $totalNilaiPenyesuaian = 0;
                            $totalPaguSetelah = 0;
                            $totalPaguTahapanTerbaru = 0;
                            $totalSelisih = 0;
                            $totalPaguDikurangi = 0;
                        @endphp

                        @foreach($data as $index => $row)
                            @php
                                $totalPaguOriginal += $row->pagu_original;
                                $totalNilaiPenyesuaian += $row->nilai_penyesuaian;
                                $totalPaguSetelah += $row->pagu_setelah_penyesuaian;
                                $totalPaguTahapanTerbaru += $row->pagu_tahapan_terbaru;
                                $paguDikurangi = $row->pagu_original - $row->pagu_tahapan_terbaru;
                                $totalPaguDikurangi += $paguDikurangi;
                                $selisih = $row->pagu_setelah_penyesuaian - $row->pagu_tahapan_terbaru;
                                $totalSelisih += $selisih;
                            @endphp
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td><a href="{{ url('/progress/opd-rek?kode_opd=' . $row->kode_skpd) }}">{{ $row->kode_skpd }}</a></td>
                                <td>{{ $row->nama_skpd }}</td>
                                <td class="text-end pagu-original">{{ number_format($row->pagu_original, 0, ',', '.') }}</td>
                                <td class="text-end">{{ number_format($row->persentase_penyesuaian, 2, ',', '.') }}%</td>
                                <td class="text-end nilai-penyesuaian">{{ number_format($row->nilai_penyesuaian, 0, ',', '.') }}</td>
                                <td class="text-end pagu-setelah">{{ number_format($row->pagu_setelah_penyesuaian, 0, ',', '.') }}</td>
                                <td class="text-end pagu-tahapan-terbaru">{{ number_format($row->pagu_tahapan_terbaru, 0, ',', '.') }}</td>
                                <td class="text-end pagu-dikurangi">{{ number_format($paguDikurangi, 0, ',', '.') }}</td>
                                <td class="text-end {{ $selisih < 0 ? 'text-red' : 'text-green' }}">{{ number_format($selisih, 0, ',', '.') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="table-dark">
                        <tr>
                            <th colspan="3" class="text-end">Total</th>
                            <th id="totalPaguOriginal" class="text-end">{{ number_format($totalPaguOriginal, 0, ',', '.') }}</th>
                            <th id="totalPersentase" class="text-end">
                                {{ number_format(($totalPaguOriginal > 0 ? ($totalNilaiPenyesuaian / $totalPaguOriginal * 100) : 0), 2, ',', '.') }}%
                            </th>
                            <th id="totalNilaiPenyesuaian" class="text-end">{{ number_format($totalNilaiPenyesuaian, 0, ',', '.') }}</th>
                            <th id="totalPaguSetelah" class="text-end">{{ number_format($totalPaguSetelah, 0, ',', '.') }}</th>
                            <th id="totalPaguTahapanTerbaru" class="text-end">{{ number_format($totalPaguTahapanTerbaru, 0, ',', '.') }}</th>
                            <th id="totalPaguDikurangi" class="text-end">{{ number_format($totalPaguDikurangi, 0, ',', '.') }}</th>
                            <th id="totalSelisih" class="text-end {{ $totalSelisih < 0 ? 'text-red' : 'text-green' }}">{{ number_format($totalSelisih, 0, ',', '.') }}</th>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- jQuery untuk DataTables & Export -->
<script>
    $(document).ready(function() {
        var table = $('#rekapTable').DataTable({
            dom: 'Bfrtip',
            buttons: [
                { extend: 'copy', text: 'Copy', className: 'btn btn-secondary' },
                { extend: 'csv', text: 'CSV', className: 'btn btn-info' },
                { extend: 'excelHtml5', text: '📊 Download Excel', className: 'btn btn-success', footer: true,
                    exportOptions: {
                        columns: [0, 2, 3, 5, 6, 7, 8, 9], // Tidak termasuk kolom persentase dan kode OPD
                        modifier: { page: 'all' },
                        format: {
                            body: function(data, row, column, node) {
                                if (column === 2) {
                                    return $(node).text().trim(); // Hanya nama OPD tanpa atribut HTML
                                }
                                return column === 0 ? row + 1 : data.replace(/\./g, '').replace(',', '.');
                            },
                            footer: function(data, row, column, node) {
                                return data.replace(/\./g, '').replace(',', '.');
                            }
                        }
                    }
                },
                { extend: 'pdfHtml5', 
                    text: '📄 Download PDF', 
                    className: 'btn btn-danger', 
                    orientation: 'landscape', 
                    pageSize: 'A4', 
                    footer: true,
                    exportOptions: { 
                        columns: [0, 2, 3, 5, 6, 7, 8, 9], // Tidak termasuk kolom persentase dan kode OPD
                        modifier: { page: 'all' },
                        format: {
                            body: function(data, row, column, node) {
                                if (column === 2) {
                                    return $(node).text().trim(); // Hanya nama OPD tanpa atribut HTML
                                }
                                return column === 0 ? row + 1 : data.replace(/\./g, '').replace(',', '.');
                            },
                            footer: function(data, row, column, node) {
                                return data.replace(/\./g, '').replace(',', '.');
                            }
                        }
                    },
                    customize: function(doc) {
                        var totalPaguOriginal = $('#totalPaguOriginal').text();
                        var totalNilaiPenyesuaian = $('#totalNilaiPenyesuaian').text();
                        var totalPaguSetelah = $('#totalPaguSetelah').text();
                        var totalPaguTahapanTerbaru = $('#totalPaguTahapanTerbaru').text();
                        var totalPaguDikurangi = $('#totalPaguDikurangi').text();
                        var totalSelisih = $('#totalSelisih').text();

                        // Customize PDF content here
                    }
                },
                { extend: 'print', text: '🖨️ Print', className: 'btn btn-primary', footer: true,
                    exportOptions: {
                        columns: [0, 2, 3, 5, 6, 7, 8, 9], // Tidak termasuk kolom persentase dan kode OPD
                        format: {
                            body: function(data, row, column, node) {
                                if (column === 2) {
                                    return $(node).text().trim(); // Hanya nama OPD tanpa atribut HTML
                                }
                                return column === 0 ? row + 1 : data.replace(/\./g, '').replace(',', '.');
                            },
                            footer: function(data, row, column, node) {
                                return data.replace(/\./g, '').replace(',', '.');
                            }
                        }
                    }
                }
            ],
            paging: false,
            searching: true,
            responsive: true,
            footerCallback: function(row, data, start, end, display) {
                var api = this.api();
                var totalPaguOriginal = 0, totalNilaiPenyesuaian = 0, totalPaguSetelah = 0, totalPaguTahapanTerbaru = 0, totalSelisih = 0, totalPaguDikurangi = 0;

                api.rows({ search: 'applied' }).every(function() {
                    var row = $(this.node());
                    var paguOriginal = parseFloat(row.find('.pagu-original').text().replace(/\./g, '').replace(',', '.')) || 0;
                    var nilaiPenyesuaian = parseFloat(row.find('.nilai-penyesuaian').text().replace(/\./g, '').replace(',', '.')) || 0;
                    var paguSetelah = parseFloat(row.find('.pagu-setelah').text().replace(/\./g, '').replace(',', '.')) || 0;
                    var paguTahapanTerbaru = parseFloat(row.find('.pagu-tahapan-terbaru').text().replace(/\./g, '').replace(',', '.')) || 0;
                    var paguDikurangi = paguOriginal - paguTahapanTerbaru;
                    var selisih = paguSetelah - paguTahapanTerbaru;

                    totalPaguOriginal += paguOriginal;
                    totalNilaiPenyesuaian += nilaiPenyesuaian;
                    totalPaguSetelah += paguSetelah;
                    totalPaguTahapanTerbaru += paguTahapanTerbaru;
                    totalPaguDikurangi += paguDikurangi;
                    totalSelisih += selisih;
                });

                var totalPersentase = totalPaguOriginal > 0 ? (totalNilaiPenyesuaian / totalPaguOriginal * 100).toFixed(2) : 0;

                $('#totalPaguOriginal').text(totalPaguOriginal.toLocaleString('id-ID'));
                $('#totalPersentase').text(totalPersentase.toLocaleString('id-ID') + '%');
                $('#totalNilaiPenyesuaian').text(totalNilaiPenyesuaian.toLocaleString('id-ID'));
                $('#totalPaguSetelah').text(totalPaguSetelah.toLocaleString('id-ID'));
                $('#totalPaguTahapanTerbaru').text(totalPaguTahapanTerbaru.toLocaleString('id-ID'));
                $('#totalPaguDikurangi').text(totalPaguDikurangi.toLocaleString('id-ID'));
                $('#totalSelisih').text(totalSelisih.toLocaleString('id-ID')).addClass(totalSelisih < 0 ? 'text-red' : 'text-green');
            }
        });
    });
</script>

@endsection
