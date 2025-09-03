@extends('layouts.app')

@section('title', 'Rekap Rekening Belanja Seluruh OPD')
@section('page-title', 'Rekap Rekening Belanja Seluruh OPD')

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
        <h4>Rekap Rekening Belanja Seluruh OPD</h4>
        <div>
            <form method="GET" action="{{ route('compare-rek') }}" class="gap-2 d-flex align-items-center">
                <div class="d-flex align-items-center">
                    <label class="me-2">Filter Tahapan:</label>
                    <select name="tahapan_id" class="form-select form-select-sm me-2">
                        <option value="">Semua Tahapan</option>
                        @foreach($tahapans as $tahapan)
                            <option value="{{ $tahapan->id }}" {{ $tahapanId == $tahapan->id ? 'selected' : '' }}>
                                {{ $tahapan->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                
                <div class="d-flex align-items-center">
                    <label class="me-2">Kata Kunci:</label>
                    <input type="text" name="keyword" value="{{ $keyword }}" 
                           placeholder="Contoh: printer, atk, meubelier atau printer atk meubelier" 
                           class="form-control form-control-sm me-2" style="width: 350px;">
                    <small class="text-muted ms-1" style="font-size: 10px;">
                        <i class="bi bi-info-circle"></i> Pisahkan dengan koma atau spasi
                    </small>
                </div>
                
                <button type="submit" class="btn btn-primary btn-sm me-2">
                    <i class="bi bi-search"></i> Cari
                </button>
                
                @if($keyword || $tahapanId)
                    <a href="{{ route('compare-rek') }}" class="btn btn-secondary btn-sm">
                        <i class="bi bi-arrow-clockwise"></i> Reset
                    </a>
                @endif
            </form>
        </div>
    </div>

    <div class="card-body">
        @if($keyword || $tahapanId)
            <div class="mb-3 alert alert-info">
                <i class="bi bi-info-circle"></i>
                <strong>Filter Aktif:</strong>
                @if($tahapanId)
                    <span class="badge bg-primary me-2">Tahapan: {{ $tahapans->find($tahapanId)->name ?? 'Tahapan ' . $tahapanId }}</span>
                @endif
                @if($keyword)
                    @php
                        $keywords = array_filter(array_map('trim', explode(',', $keyword)));
                        if (empty($keywords)) {
                            $keywords = array_filter(array_map('trim', explode(' ', $keyword)));
                        }
                        $keywordCount = count($keywords);
                    @endphp
                    <span class="badge bg-success me-2">
                        Kata Kunci: "{{ $keyword }}" 
                        <small>({{ $keywordCount }} kata)</small>
                    </span>
                @endif
                <span class="badge bg-secondary">Total Data: {{ $rekap->count() }}</span>
            </div>
        @else
            <div class="mb-3 alert alert-warning">
                <i class="bi bi-exclamation-triangle"></i>
                <strong>Silakan tentukan filter terlebih dahulu!</strong>
                <br>
                Masukkan kata kunci pencarian atau pilih tahapan untuk menampilkan data.
                <br>
                <small class="text-muted">
                    <strong>Tips pencarian:</strong> 
                    Gunakan koma untuk memisahkan kata kunci (contoh: "printer, atk, meubelier") 
                    atau spasi untuk pencarian yang lebih fleksibel (contoh: "printer atk meubelier")
                </small>
            </div>
        @endif
        
        <div class="table-container">
            @if($rekap->isNotEmpty() && ($keyword || $tahapanId))
                <table id="rekapTable" class="table table-striped table-bordered">
                    <thead>
                        <tr>
                            <th style="text-align: center; width: 50px;">No</th>
                            <th style="text-align: center; width: 100px;">Kode SKPD</th>
                            <th style="text-align: center; width: 120px;">Nama SKPD</th>
                            <th style="text-align: center; width: 120px;">Kode Rekening</th>
                            <th style="text-align: center; width: 200px;">Nama Rekening</th>
                            <th style="text-align: center; width: 150px;">Nama Standar Harga</th>
                            @if($tahapanId)
                                <th style="text-align: center; width: 120px;">
                                    {{ $tahapans->find($tahapanId)->name ?? 'Tahapan ' . $tahapanId }}
                                </th>
                            @else
                                @foreach($availableTahapans as $tahapanId)
                                    <th style="text-align: center; width: 120px;">
                                        {{ $tahapans->find($tahapanId)->name ?? 'Tahapan ' . $tahapanId }}
                                    </th>
                                @endforeach
                            @endif
                        </tr>
                    </thead>
                                        <tbody>
                        @php 
                            $no = 1;
                            $currentSkpd = null;
                            $skpdTotal = 0;
                        @endphp
                        @foreach($rekap as $item)
                            @if($currentSkpd !== null && $currentSkpd !== $item->kode_skpd)
                                <!-- Tampilkan total SKPD sebelumnya -->
                                <tr class="table-warning fw-bold">
                                    <td colspan="6" class="text-end">
                                        <strong>TOTAL {{ $currentSkpd }} - {{ $rekap->where('kode_skpd', $currentSkpd)->first()->nama_skpd }}</strong>
                                    </td>
                                    @if($tahapanId)
                                        <td class="text-end table-warning">
                                            <strong>{{ number_format($skpdTotal, 2, ',', '.') }}</strong>
                                        </td>
                                    @else
                                        @foreach($availableTahapans as $tahapanId)
                                            @php
                                                $totalPerTahapanSkpd = $rekap->where('kode_skpd', $currentSkpd)
                                                    ->where('tahapan_id', $tahapanId)
                                                    ->sum('total_pagu');
                                            @endphp
                                            <td class="text-end table-warning">
                                                <strong>{{ number_format($totalPerTahapanSkpd, 2, ',', '.') }}</strong>
                                            </td>
                                        @endforeach
                                    @endif
                                </tr>
                                @php $skpdTotal = 0; @endphp
                            @endif
                            
                            <tr>
                                <td class="text-center">{{ $no++ }}</td>
                                <td class="text-center">{{ $item->kode_skpd }}</td>
                                <td>{{ $item->nama_skpd }}</td>
                                <td>{{ $item->kode_rekening }}</td>
                                <td>{{ $item->nama_rekening }}</td>
                                <td>{{ $item->nama_standar_harga }}</td>
                                @if($tahapanId)
                                    <td class="text-end">
                                        {{ number_format($item->total_pagu, 2, ',', '.') }}
                                    </td>
                                    @php $skpdTotal += $item->total_pagu; @endphp
                                @else
                                    @foreach($availableTahapans as $tahapanId)
                                        @php
                                            $nilai = ($item->tahapan_id == $tahapanId) ? $item->total_pagu : 0;
                                        @endphp
                                        <td class="text-end">
                                            {{ $nilai ? number_format($nilai, 2, ',', '.') : '-' }}
                                        </td>
                                    @endforeach
                                @endif
                            </tr>
                            @php $currentSkpd = $item->kode_skpd; @endphp
                        @endforeach
                        
                        <!-- Tampilkan total SKPD terakhir -->
                        @if($currentSkpd !== null)
                            <tr class="table-warning fw-bold">
                                <td colspan="6" class="text-end">
                                    <strong>TOTAL {{ $currentSkpd }} - {{ $rekap->where('kode_skpd', $currentSkpd)->first()->nama_skpd }}</strong>
                                </td>
                                @if($tahapanId)
                                    <td class="text-end table-warning">
                                        <strong>{{ number_format($skpdTotal, 2, ',', '.') }}</strong>
                                    </td>
                                @else
                                    @foreach($availableTahapans as $tahapanId)
                                        @php
                                            $totalPerTahapanSkpd = $rekap->where('kode_skpd', $currentSkpd)
                                                ->where('tahapan_id', $tahapanId)
                                                ->sum('total_pagu');
                                        @endphp
                                        <td class="text-end table-warning">
                                            <strong>{{ number_format($totalPerTahapanSkpd, 2, ',', '.') }}</strong>
                                        </td>
                                    @endforeach
                                @endif
                            </tr>
                        @endif
                    </tbody>
                    <tfoot>
                        <tr class="table-secondary fw-bold">
                            <th colspan="6" class="text-center">TOTAL</th>
                            @if($tahapanId)
                                <th class="text-end">
                                    {{ number_format($totalPerTahapan[$tahapanId] ?? 0, 2, ',', '.') }}
                                </th>
                            @else
                                @foreach($availableTahapans as $tahapanId)
                                    <th class="text-end">
                                        {{ number_format($totalPerTahapan[$tahapanId] ?? 0, 2, ',', '.') }}
                                    </th>
                                @endforeach
                            @endif
                        </tr>
                        <tr class="table-dark fw-bold">
                            <th colspan="6" class="text-center">GRAND TOTAL</th>
                            @if($tahapanId)
                                <th class="text-end">
                                    {{ number_format($grandTotal, 2, ',', '.') }}
                                </th>
                            @else
                                <th colspan="{{ count($availableTahapans) }}" class="text-end">
                                    {{ number_format($grandTotal, 2, ',', '.') }}
                                </th>
                            @endif
                        </tr>
                    </tfoot>
                </table>
            @else
                <div class="text-center alert alert-warning">
                    <i class="bi bi-exclamation-triangle"></i>
                    @if($keyword || $tahapanId)
                        Tidak ada data yang sesuai dengan filter yang diterapkan. 
                        Silakan coba kata kunci lain atau reset filter.
                    @else
                        Data tidak ditampilkan karena belum ada filter yang diterapkan.
                    @endif
                </div>
            @endif
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
