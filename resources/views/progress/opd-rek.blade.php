@extends('layouts.app')

@section('title', 'Set % Rek Belanja Per OPD')
@section('page-title', 'Set % Rek Belanja Per OPD')

@section('content')

<!-- Import DataTables & Buttons -->
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.3.6/css/buttons.bootstrap5.min.css">
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.3.6/js/dataTables.buttons.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.3.6/js/buttons.html5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.3.6/js/buttons.print.min.js"></script>

<style>
    .table-sm th, .table-sm td {
        padding: 6px 10px;
        font-size: 12px;
        white-space: nowrap;
    }

    td.nama-rekening {
        max-width: 250px;
        white-space: normal;
    }
</style>

<div class="container">
    <div class="card">
        <div class="card-header">
            <h5 class="card-title">Set % Rekening Belanja Per OPD</h5>
        </div>
        <div class="card-body">

        <!-- Form Filter -->
        <form id="filter-form" class="row g-3 mb-3">
            <div class="col-md-4">
                <label for="kode_opd" class="form-label">Pilih OPD</label>
                <select name="kode_opd" id="kode_opd" class="form-select">
                    <option value="">Silakan pilih OPD</option>
                    @foreach ($opds as $opd)
                        <option value="{{ $opd->kode_skpd }}" {{ request('kode_opd') == $opd->kode_skpd ? 'selected' : '' }}>
                            {{ $opd->kode_skpd }} - {{ $opd->nama_skpd }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-4 d-flex align-items-end">
                <button type="submit" class="btn btn-primary w-100">Filter</button>
            </div>
            <div class="col-md-4 d-flex align-items-end">
                <a href="{{ route('progress.index') }}" class="btn btn-secondary w-100">Kembali</a>
            </div>
        </form>

        @if (request('kode_opd'))
            <!-- Form Update Persentase -->
            <form id="update-form" action="{{ route('simulasi.set-opd-rek.update') }}" method="POST">
                @csrf
                <input type="hidden" name="kode_opd" value="{{ request('kode_opd') }}">

                <div class="table-responsive">
                    <table id="rekapTable" class="table table-striped table-bordered table-sm">
                        <thead class="table-dark">
                            <tr>
                                <th>No</th>
                                <th>Kode Rekening</th>
                                <th>Nama Rekening</th>
                                <th>Pagu Murni</th>
                                <th>Persentase</th>
                                <th>Pagu Pengurangan</th>
                                <th>Pagu Setelah Pengurangan</th>
                                <th>Pagu Pergeseran</th>
                                <th>Pagu yang sudah dikurangi<br>(Murni-Entri SIPD)</th>
                                <th>Selisih</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($data as $index => $row)
                                @php
                                    $nilai_penyesuaian = ($row->pagu_original * $row->persentase_penyesuaian) / 100;
                                    $pagu_setelah = $row->pagu_original - $nilai_penyesuaian;
                                    $pagu_dikurangi = $row->pagu_original - $row->pagu_terbaru;
                                    $selisih = $pagu_setelah - $row->pagu_terbaru;
                                @endphp
                                <tr>
                                    <td>{{ $index + 1 }}</td>
                                    <td>{{ $row->kode_rekening }}</td>
                                    <td class="nama-rekening">{{ $row->nama_rekening }}</td>
                                    <td class="text-end pagu-original" data-value="{{ $row->pagu_original }}">
                                        {{ number_format($row->pagu_original, 0, ',', '.') }}
                                    </td>
                                    <td class="text-center">{{ number_format($row->persentase_penyesuaian, 2, ',', '.') }}%</td>
                                    <td class="text-end nilai-penyesuaian">
                                        {{ number_format($nilai_penyesuaian, 0, ',', '.') }}
                                    </td>
                                    <td class="text-end pagu-setelah">
                                        {{ number_format($pagu_setelah, 0, ',', '.') }}
                                    </td>
                                    <td class="text-end pagu-terbaru" data-value="{{ $row->pagu_terbaru }}">
                                        {{ number_format($row->pagu_terbaru, 0, ',', '.') }}
                                    </td>
                                    <td class="text-end pagu-dikurangi">
                                        {{ number_format($pagu_dikurangi, 0, ',', '.') }}
                                    </td>
                                    <td class="text-end selisih">
                                        {{ number_format($selisih, 0, ',', '.') }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot class="table-dark">
                            <tr>
                                <th colspan="3" class="text-end">Total:</th>
                                <th id="total-pagu-original" class="text-end">0</th>
                                <th class="text-end">-</th>
                                <th id="total-nilai-penyesuaian" class="text-end">0</th>
                                <th id="total-pagu-setelah" class="text-end">0</th>
                                <th id="total-pagu-terbaru" class="text-end">0</th>
                                <th id="total-pagu-dikurangi" class="text-end">0</th>
                                <th id="total-selisih" class="text-end">0</th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </form>
        @else
            <div class="alert alert-info">Silakan pilih OPD untuk menampilkan data.</div>
        @endif
    </div>
    </div>
</div>

<script>
    function formatNumber(value) {
        return new Intl.NumberFormat('id-ID').format(value);
    }

    function hitungTotal() {
        let totalPaguOriginal = 0;
        let totalPaguTerbaru = 0;
        let totalNilaiPenyesuaian = 0;
        let totalPaguSetelah = 0;
        let totalPaguDikurangi = 0;
        let totalSelisih = 0;

        $('tbody tr').each(function() {
            let row = $(this);
            totalPaguOriginal += parseFloat(row.find('.pagu-original').attr('data-value')) || 0;
            totalPaguTerbaru += parseFloat(row.find('.pagu-terbaru').attr('data-value')) || 0;
            totalNilaiPenyesuaian += parseFloat(row.find('.nilai-penyesuaian').text().replace(/\./g, '')) || 0;
            totalPaguSetelah += parseFloat(row.find('.pagu-setelah').text().replace(/\./g, '')) || 0;
            totalPaguDikurangi += parseFloat(row.find('.pagu-dikurangi').text().replace(/\./g, '')) || 0;
            totalSelisih += parseFloat(row.find('.selisih').text().replace(/\./g, '')) || 0;
        });

        $('#total-pagu-original').text(formatNumber(totalPaguOriginal));
        $('#total-pagu-terbaru').text(formatNumber(totalPaguTerbaru));
        $('#total-nilai-penyesuaian').text(formatNumber(totalNilaiPenyesuaian));
        $('#total-pagu-setelah').text(formatNumber(totalPaguSetelah));
        $('#total-pagu-dikurangi').text(formatNumber(totalPaguDikurangi));
        $('#total-selisih').text(formatNumber(totalSelisih));
    }

    $(document).ready(function() {
        hitungTotal();

        $('#rekapTable').DataTable({
            dom: 'Bfrtip',
            buttons: [
                { extend: 'copy', text: 'Copy', className: 'btn btn-secondary' },
                { extend: 'csv', text: 'CSV', className: 'btn btn-info' },
                { extend: 'excelHtml5', text: '📊 Download Excel', className: 'btn btn-success', footer: true,
                    exportOptions: {
                        columns: [0, 1, 2, 3, 5, 6, 7, 8, 9], // Tidak termasuk kolom persentase
                        modifier: { page: 'all' },
                        format: {
                            body: function(data, row, column, node) {
                                return column === 2 ? $(node).text().trim() : data.replace(/\./g, '').replace(',', '.');
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
                        columns: [0, 1, 2, 3, 5, 6, 7, 8, 9], // Tidak termasuk kolom persentase
                        modifier: { page: 'all' },
                        format: {
                            body: function(data, row, column, node) {
                                return column === 2 ? $(node).text().trim() : data.replace(/\./g, '').replace(',', '.');
                            },
                            footer: function(data, row, column, node) {
                                return data.replace(/\./g, '').replace(',', '.');
                            }
                        }
                    },
                    customize: function(doc) {
                        var totalPaguOriginal = $('#total-pagu-original').text();
                        var totalNilaiPenyesuaian = $('#total-nilai-penyesuaian').text();
                        var totalPaguSetelah = $('#total-pagu-setelah').text();
                        var totalPaguTerbaru = $('#total-pagu-terbaru').text();
                        var totalPaguDikurangi = $('#total-pagu-dikurangi').text();
                        var totalSelisih = $('#total-selisih').text();

                        // Customize PDF content here
                    }
                },
                { extend: 'print', text: '🖨️ Print', className: 'btn btn-primary', footer: true,
                    exportOptions: {
                        columns: [0, 1, 2, 3, 5, 6, 7, 8, 9], // Tidak termasuk kolom persentase
                        format: {
                            body: function(data, row, column, node) {
                                return column === 2 ? $(node).text().trim() : data.replace(/\./g, '').replace(',', '.');
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
            responsive: true
        });
    });
</script>

@endsection
