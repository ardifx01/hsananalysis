@extends('layouts.app')

@section('title', 'Simulasi Belanja per OPD')
@section('page-title', 'Simulasi Belanja per OPD')

@section('content')
<div class="card" data-aos="fade-up" data-aos-delay="300">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h4>Simulasi Belanja per OPD</h4>
        <div class="gap-2 d-flex">
            <form method="GET" action="" class="flex-wrap gap-2 d-flex align-items-center">
                <label for="tahapan_id" class="mb-0 me-2">Filter Tahapan:</label>
                <select name="tahapan_id" id="tahapan_id" class="form-select form-select-sm me-2" onchange="this.form.submit()">
                    <option value="">Pilih Tahapan</option>
                    @foreach($tahapans as $tahapan)
                        <option value="{{ $tahapan->id }}" {{ $tahapanId == $tahapan->id ? 'selected' : '' }}>{{ $tahapan->name }}</option>
                    @endforeach
                </select>
            </form>
            <div class="btn-group">
                <button class="btn btn-success btn-sm" onclick="exportToExcel()">
                    <i class="bi bi-file-excel"></i> Export Excel
                </button>
                <button class="btn btn-danger btn-sm" onclick="exportToPDF()">
                    <i class="bi bi-file-pdf"></i> Export PDF
                </button>
            </div>
        </div>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table align-middle table-sm table-bordered table-striped" id="rekapTable">
                <thead class="table-primary">
                    <tr>
                        <th style="width:40px">No</th>
                        <th style="width:120px">Kode OPD</th>
                        <th>Nama OPD</th>
                        <th class="text-end" style="width:180px">Total Pagu</th>
                        <th class="text-end" style="width:180px">Realisasi</th>
                        <th class="text-end" style="width:180px">Anggaran-Realisasi</th>
                        <th class="text-end" style="width:180px">Penyesuaian</th>
                        <th class="text-end" style="width:180px">Proyeksi Perubahan</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $totalPenyesuaian = 0;
                        $totalRealisasi = 0;
                        $totalAnggaranRealisasi = 0;
                    @endphp
                    @foreach($rekapOpd as $i => $opd)
                    @php
                        $anggaranRealisasi = ($opd->total_pagu ?? 0) - ($opd->total_realisasi ?? 0);
                        $proyeksiPerubahan = $anggaranRealisasi + ($opd->total_penyesuaian ?? 0);
                    @endphp
                    <tr>
                        <td>{{ $i + 1 }}</td>
                        <td>{{ $opd->kode_skpd }}</td>
                        <td>{{ $opd->nama_skpd }}</td>
                        <td class="text-end">{{ number_format($opd->total_pagu, 2, ',', '.') }}</td>
                        <td class="text-end">{{ number_format($opd->total_realisasi ?? 0, 2, ',', '.') }}</td>
                        <td class="text-end">{{ number_format($anggaranRealisasi, 2, ',', '.') }}</td>
                        <td class="text-end">{{ number_format($opd->total_penyesuaian ?? 0, 2, ',', '.') }}</td>
                        <td class="text-end">{{ number_format($proyeksiPerubahan, 2, ',', '.') }}</td>
                        @php
                            $totalPenyesuaian += $opd->total_penyesuaian ?? 0;
                            $totalRealisasi += $opd->total_realisasi ?? 0;
                            $totalAnggaranRealisasi += $anggaranRealisasi;
                            $totalProyeksiPerubahan = ($totalProyeksiPerubahan ?? 0) + $proyeksiPerubahan;
                        @endphp
                    </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr class="table-secondary">
                        <td></td>
                        <td></td>
                        <td class="text-end">Total</td>
                        <td class="text-end">{{ number_format($rekapOpd->sum('total_pagu'), 2, ',', '.') }}</td>
                        <td class="text-end">{{ number_format($totalRealisasi, 2, ',', '.') }}</td>
                        <td class="text-end">{{ number_format($totalAnggaranRealisasi, 2, ',', '.') }}</td>
                        <td class="text-end">{{ number_format($totalPenyesuaian, 2, ',', '.') }}</td>
                        <td class="text-end">{{ number_format($totalProyeksiPerubahan ?? 0, 2, ',', '.') }}</td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>

@push('scripts')
<script>
$(document).ready(function() {
    $('#rekapTable').DataTable({
        dom: 'Bfrtip',
        buttons: [
            {
                extend: 'excelHtml5',
                text: '📊 Export Excel',
                className: 'btn btn-success',
                footer: true,
                exportOptions: {
                    columns: ':visible',
                    format: {
                        body: function(data, row, column, node) {
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
        info: false
    });
});

function exportToExcel() {
    $('.buttons-excel').click();
}

function formatNumber(num) {
    return num.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".").replace(',', '.');
}

function exportToPDF() {
    // Get the table data
    var table = $('#rekapTable').DataTable();
    var data = table.buttons.exportData({
        columns: ':visible',
        format: {
            body: function(data, row, column, node) {
                if (column === 0) return row + 1;
                if (column >= 3) {
                    // Keep the original format with dots and commas
                    return data;
                }
                return data;
            },
            footer: function(data, row, column, node) {
                if (column >= 3) {
                    // Keep the original format with dots and commas
                    return data;
                }
                return data;
            }
        }
    });

    // Get the total row data from the table
    var totalRow = $('#rekapTable tfoot tr').find('td').map(function() {
        return $(this).text();
    }).get();

    // Create PDF document
    var docDefinition = {
        pageOrientation: 'landscape',
        pageSize: 'A4',
        content: [
            {
                text: 'Simulasi Belanja per OPD',
                style: 'header',
                alignment: 'center',
                margin: [0, 0, 0, 10]
            },
            {
                table: {
                    headerRows: 1,
                    widths: ['5%', '15%', '35%', '15%', '15%', '15%'],
                    body: [
                        // Header
                        [
                            { text: 'No', style: 'tableHeader' },
                            { text: 'Kode OPD', style: 'tableHeader' },
                            { text: 'Nama OPD', style: 'tableHeader' },
                            { text: 'Total Pagu', style: 'tableHeader' },
                            { text: 'Realisasi', style: 'tableHeader' },
                            { text: 'Penyesuaian', style: 'tableHeader' },
                            { text: 'Proyeksi Perubahan', style: 'tableHeader' }
                        ],
                        // Body
                        ...data.body.map(row => [
                            { text: row[0], alignment: 'center' },
                            { text: row[1] },
                            { text: row[2] },
                            { text: row[3], alignment: 'right' },
                            { text: row[4], alignment: 'right' },
                            { text: row[5], alignment: 'right' },
                            { text: row[6], alignment: 'right' }
                        ]),
                        // Footer
                        [
                            { text: '', colSpan: 3, style: 'tableFooter' },
                            {},
                            {},
                            { text: totalRow[3], alignment: 'right', style: 'tableFooter' },
                            { text: totalRow[4], alignment: 'right', style: 'tableFooter' },
                            { text: totalRow[5], alignment: 'right', style: 'tableFooter' },
                            { text: totalRow[6], alignment: 'right', style: 'tableFooter' }
                        ]
                    ]
                },
                layout: {
                    hLineWidth: function(i, node) { return 0.5; },
                    vLineWidth: function(i, node) { return 0.5; },
                    hLineColor: function(i, node) { return '#aaa'; },
                    vLineColor: function(i, node) { return '#aaa'; },
                    paddingLeft: function(i, node) { return 4; },
                    paddingRight: function(i, node) { return 4; },
                    paddingTop: function(i, node) { return 2; },
                    paddingBottom: function(i, node) { return 2; },
                    fillColor: function(rowIndex, node, columnIndex) {
                        return (rowIndex === 0) ? '#428bca' : null;
                    }
                }
            }
        ],
        styles: {
            header: {
                fontSize: 14,
                bold: true,
                margin: [0, 0, 0, 10]
            },
            tableHeader: {
                bold: true,
                fontSize: 9,
                color: 'white',
                fillColor: '#428bca',
                alignment: 'center'
            },
            tableFooter: {
                bold: true,
                fontSize: 9,
                fillColor: '#f2f2f2'
            }
        },
        defaultStyle: {
            fontSize: 8
        }
    };

    // Generate and download PDF
    pdfMake.createPdf(docDefinition).download('simulasi-belanja-opd.pdf');
}
</script>
@endpush
@endsection 