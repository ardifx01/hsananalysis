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
                        <th class="text-end" style="width:180px">Penyesuaian</th>
                        <th class="text-end" style="width:180px">Pagu Setelah Penyesuaian</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $totalPenyesuaian = 0;
                        $totalPaguSetelah = 0;
                    @endphp
                    @foreach($rekapOpd as $i => $opd)
                    <tr>
                        <td>{{ $i + 1 }}</td>
                        <td>{{ $opd->kode_skpd }}</td>
                        <td>{{ $opd->nama_skpd }}</td>
                        <td class="text-end">{{ number_format($opd->total_pagu, 2, ',', '.') }}</td>
                        <td class="text-end">{{ number_format($opd->total_penyesuaian ?? 0, 2, ',', '.') }}</td>
                        <td class="text-end">{{ number_format($opd->total_pagu_setelah_penyesuaian ?? $opd->total_pagu, 2, ',', '.') }}</td>
                        @php
                            $totalPenyesuaian += $opd->total_penyesuaian ?? 0;
                            $totalPaguSetelah += $opd->total_pagu_setelah_penyesuaian ?? $opd->total_pagu;
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
                        <td class="text-end">{{ number_format($totalPenyesuaian, 2, ',', '.') }}</td>
                        <td class="text-end">{{ number_format($totalPaguSetelah, 2, ',', '.') }}</td>
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
            },
            {
                extend: 'pdfHtml5',
                text: '📄 Export PDF',
                className: 'btn btn-danger',
                orientation: 'landscape',
                pageSize: 'A4',
                footer: true,
                exportOptions: {
                    columns: ':visible',
                    format: {
                        body: function(data, row, column, node) {
                            if (column === 0) return row + 1;
                            if (column >= 3) { // Kolom angka (total pagu, penyesuaian, pagu setelah)
                                // Hapus semua karakter non-angka kecuali koma
                                let cleanData = data.replace(/[^\d,]/g, '');
                                // Ganti koma dengan titik untuk format angka
                                return cleanData.replace(',', '.');
                            }
                            return data;
                        },
                        footer: function(data, row, column, node) {
                            if (column >= 3) { // Kolom angka di footer
                                let cleanData = data.replace(/[^\d,]/g, '');
                                return cleanData.replace(',', '.');
                            }
                            return data;
                        }
                    }
                },
                customize: function(doc) {
                    // Set font size
                    doc.defaultStyle.fontSize = 8;
                    doc.styles.tableHeader.fontSize = 9;
                    
                    // Set column widths
                    doc.content[1].table.widths = ['5%', '15%', '35%', '15%', '15%', '15%'];
                    
                    // Set alignment
                    doc.styles.tableHeader.alignment = 'center';
                    doc.styles.tableBody.alignment = 'right';
                    
                    // Set number format for numeric columns
                    doc.content[1].table.body.forEach(function(row, i) {
                        if (i > 0) { // Skip header row
                            for (let j = 3; j < row.length; j++) { // Start from index 3 (numeric columns)
                                if (row[j].text) {
                                    // Format number with thousand separator and 2 decimal places
                                    let num = parseFloat(row[j].text.replace(/\./g, '').replace(',', '.'));
                                    row[j].text = num.toLocaleString('id-ID', {
                                        minimumFractionDigits: 2,
                                        maximumFractionDigits: 2
                                    });
                                }
                            }
                        }
                    });
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

function exportToPDF() {
    $('.buttons-pdf').click();
}
</script>
@endpush
@endsection 