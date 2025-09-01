@extends('layouts.app')

@section('title', 'Rekapitulasi Struktur Semua OPD')
@section('page-title', 'Rekapitulasi Struktur Semua OPD')

@section('content')
<div class="card" data-aos="fade-up" data-aos-delay="300">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h4>Rekapitulasi Struktur Semua OPD</h4>
        <form method="GET" action="" class="flex-wrap gap-2 d-flex align-items-center">
            <label for="tahapan_id" class="mb-0 me-2">Filter Tahapan:</label>
            <select name="tahapan_id" id="tahapan_id" class="form-select form-select-sm me-2" onchange="this.form.submit()">
                <option value="">Pilih Tahapan</option>
                @foreach($tahapans as $tahapan)
                    <option value="{{ $tahapan->id }}" {{ $tahapanId == $tahapan->id ? 'selected' : '' }}>{{ $tahapan->name }}</option>
                @endforeach
            </select>
        </form>
    </div>
    <div class="card-body">
        @if($tahapanId)
            <div class="mb-3">
                <div class="btn-group">
                    <button class="btn btn-primary btn-sm" onclick="window.print()">
                        <i class="bi bi-printer"></i> Cetak
                    </button>
                    <button class="btn btn-danger btn-sm" onclick="exportToPDF()">
                        <i class="bi bi-file-pdf"></i> Export PDF
                    </button>
                    <a href="{{ route('simulasi.rekapitulasi-struktur-opd.export-excel', ['tahapan_id' => $tahapanId]) }}" class="btn btn-success btn-sm">
                        <i class="bi bi-file-excel"></i> Export Excel
                    </a>
                </div>
            </div>
        @endif
        
        @if($tahapanId)
            <div id="print-area">
                <div class="mb-2">
                    <strong>Tahapan:</strong> {{ $tahapans->where('id', $tahapanId)->first()->name ?? '-' }}
                </div>
                
                <div class="table-responsive" style="max-height: 80vh; overflow-y: auto;">
                    <table class="table table-sm table-bordered table-hover">
                        <thead class="table-primary">
                            <tr>
                                <th style="width: 50px;">No</th>
                                <th style="width: 120px;">Kode OPD</th>
                                <th style="min-width: 200px;">Nama OPD</th>
                                @foreach($kodeRekenings as $kr)
                                    @if(count(explode('.', $kr->kode_rekening)) === 3)
                                        <th style="width: 120px;" class="text-center" title="{{ $kr->uraian }}">
                                            {{ $kr->kode_rekening }}<br>
                                            <small>{{ \Illuminate\Support\Str::limit($kr->uraian, 15) }}</small>
                                        </th>
                                    @endif
                                @endforeach
                                <th style="width: 120px;" class="text-center table-secondary">Total Anggaran</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($rekapitulasiData as $i => $opd)
                                <tr>
                                    <td class="text-center">{{ $i + 1 }}</td>
                                    <td>{{ $opd['kode_skpd'] }}</td>
                                    <td>{{ $opd['nama_skpd'] }}</td>
                                    @foreach($kodeRekenings as $kr)
                                        @if(count(explode('.', $kr->kode_rekening)) === 3)
                                            @php
                                                $strukturData = $opd['struktur_belanja'][$kr->kode_rekening] ?? null;
                                                $anggaran = $strukturData ? $strukturData['anggaran'] : 0;
                                            @endphp
                                            <td class="text-end">
                                                {{ $anggaran ? number_format($anggaran, 2, ',', '.') : '-' }}
                                            </td>
                                        @endif
                                    @endforeach
                                    <td class="text-end table-secondary fw-bold">
                                        {{ number_format($opd['total_anggaran'], 2, ',', '.') }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr class="table-secondary fw-bold">
                                <th colspan="3" class="text-center">TOTAL</th>
                                @foreach($kodeRekenings as $kr)
                                    @if(count(explode('.', $kr->kode_rekening)) === 3)
                                        @php
                                            $totalPerRekening = $rekapitulasiData->sum(function($opd) use ($kr) {
                                                $strukturData = $opd['struktur_belanja'][$kr->kode_rekening] ?? null;
                                                return $strukturData ? $strukturData['anggaran'] : 0;
                                            });
                                        @endphp
                                        <th class="text-end">
                                            {{ number_format($totalPerRekening, 2, ',', '.') }}
                                        </th>
                                    @endif
                                @endforeach
                                <th class="text-end">
                                    {{ number_format($rekapitulasiData->sum('total_anggaran'), 2, ',', '.') }}
                                </th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        @else
            <div class="alert alert-info">
                <i class="bi bi-info-circle"></i> Silakan pilih tahapan untuk melihat rekapitulasi struktur semua OPD.
            </div>
        @endif
    </div>
</div>

@push('styles')
<style>
    .table-sm th, .table-sm td {
        font-size: 10px !important;
        padding: 0.3rem !important;
    }

    /* Table styles for better PDF export */
    .table-responsive {
        margin-bottom: 1rem;
    }
    
    .table {
        width: 100% !important;
        margin-bottom: 0 !important;
    }
    
    .table th,
    .table td {
        white-space: nowrap;
        padding: 0.3rem !important;
        border: 1px solid #dee2e6;
    }
    
    .table thead th {
        background-color: #f8f9fa;
        border-bottom: 2px solid #dee2e6;
        vertical-align: middle;
    }
    
    .table tbody tr:nth-of-type(odd) {
        background-color: rgba(0,0,0,.02);
    }
    
    .table tfoot th {
        background-color: #f8f9fa;
        border-top: 2px solid #dee2e6;
    }

    /* Print styles */
    @media print {
        body * {
            visibility: hidden !important;
        }
        #print-area, #print-area * {
            visibility: visible !important;
        }
        #print-area {
            position: absolute;
            left: 0;
            top: 0;
            width: 100%;
        }
        .table {
            page-break-inside: avoid;
        }
        .table-responsive {
            overflow: visible !important;
        }
    }
</style>
@endpush

@push('scripts')
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.8.2/jspdf.plugin.autotable.min.js"></script>
<script>

    // Fungsi untuk export ke PDF
    window.exportToPDF = async function() {
        try {
            // Tampilkan loading
            const loadingDiv = $('<div>')
                .addClass('position-fixed top-50 start-50 translate-middle')
                .css({
                    'z-index': '9999',
                    'background': 'rgba(255,255,255,0.8)',
                    'padding': '20px',
                    'border-radius': '5px',
                    'box-shadow': '0 0 10px rgba(0,0,0,0.1)'
                })
                .html('<div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div><div class="mt-2">Mempersiapkan PDF...</div>');
            $('body').append(loadingDiv);

            const { jsPDF } = window.jspdf;
            const pdf = new jsPDF('l', 'mm', 'a4'); // Landscape orientation
            const margin = 10;
            let firstTable = true;

            // Ambil semua tabel di halaman
            const tables = document.querySelectorAll('table');

            for (let i = 0; i < tables.length; i++) {
                const table = tables[i];

                // Ambil judul tabel dari <h5> terdekat di atas <table>
                let title = '';
                let prev = table.previousSibling;
                while (prev) {
                    if (prev.nodeType === 1 && prev.tagName && prev.tagName.toUpperCase() === 'H5') {
                        title = prev.innerText.trim();
                        break;
                    }
                    prev = prev.previousSibling;
                }
                if (!title) title = 'Tabel ' + (i + 1);

                // Ambil header dan data
                const headers = [];
                table.querySelectorAll('thead tr th').forEach(th => {
                    headers.push(th.innerText.trim());
                });

                const body = [];
                table.querySelectorAll('tbody tr').forEach(tr => {
                    const row = [];
                    tr.querySelectorAll('td').forEach(td => {
                        row.push(td.innerText.trim());
                    });
                    if (row.length) body.push(row);
                });

                // Ambil footer jika ada
                let foot = [];
                const tfoot = table.querySelector('tfoot');
                if (tfoot) {
                    tfoot.querySelectorAll('tr').forEach(tr => {
                        const row = [];
                        let colCount = headers.length;
                        let cells = tr.querySelectorAll('th,td');
                        for (let i = 0; i < colCount; i++) {
                            row[i] = cells[i] ? cells[i].innerText.trim() : '';
                        }
                        foot.push(row);
                    });
                }

                // Tambahkan judul tabel
                if (!firstTable) pdf.addPage();
                pdf.setFontSize(12);
                pdf.text(title, margin, 18);

                // Render tabel dengan autotable
                pdf.autoTable({
                    startY: 22,
                    head: [headers],
                    body: body,
                    foot: foot,
                    margin: { left: margin, right: margin },
                    styles: { fontSize: 6, cellPadding: 1 },
                    headStyles: { fillColor: [41, 128, 185], textColor: 255 },
                    theme: 'grid',
                    showHead: 'everyPage',
                    showFoot: 'lastPage',
                    didDrawPage: function (data) {
                        if (data.pageNumber === 1) {
                            pdf.setFontSize(12);
                            pdf.text(title, margin, 18);
                        } else {
                            data.settings.margin.top = 16;
                        }
                    }
                });

                firstTable = false;
            }

            pdf.save('rekapitulasi-struktur-semua-opd.pdf');
            loadingDiv.remove();
        } catch (error) {
            alert('Terjadi kesalahan saat membuat PDF: ' + error.message);
            if (typeof loadingDiv !== 'undefined') loadingDiv.remove();
        }
    }
</script>
@endpush
@endsection
