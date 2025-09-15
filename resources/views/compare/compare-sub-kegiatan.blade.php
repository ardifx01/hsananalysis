@extends('layouts.app')

@section('title', 'Perbandingan Pagu Berdasarkan Kode Sub Kegiatan')
@section('page-title', 'Perbandingan Pagu Sub Kegiatan')

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
        padding: 6px 8px; /* Perkecil padding */
        text-align: left;
        border-bottom: 1px solid #ddd;
        font-size: 12px; /* Perkecil ukuran font */
        white-space: normal; /* Biarkan teks wrap */
        word-wrap: break-word; /* Pastikan teks panjang tidak membuat kolom melebar */
    }

    th {
        background-color: #0056b3 !important; /* Warna lebih soft */
        color: white;
        font-weight: bold;
        text-align: center;
    }

    tr:hover {
        background-color: #f1f1f1;
    }

    .total-container {
        margin-top: 15px;
        font-size: 14px;
        font-weight: bold;
        text-align: right;
    }

    .dt-buttons {
        margin-bottom: 10px;
    }

    .dt-buttons .btn {
        margin-right: 5px;
    }
</style>
<div class="card" data-aos="fade-up" data-aos-delay="800">
 <div class="card-body">
<!-- Form Filter Berdasarkan OPD -->
<form action="{{ route('compare.sub-kegiatan') }}" method="GET" class="mb-3">
<div class="row">
<div class="col-md-12">
    <label for="kode_opd">Pilih OPD:</label>
    <select name="kode_opd" id="kode_opd" class="form-select">
        <option value="">Semua OPD</option>
        @foreach($opds as $opd)
            <option value="{{ $opd->kode_skpd }}" {{ request('kode_opd') == $opd->kode_skpd ? 'selected' : '' }}>
                {{ $opd->kode_skpd }} - {{ $opd->nama_skpd }}
            </option>
        @endforeach
    </select>
    </div>
    </div>
    <div class="row">
    <div class="col-md-6">
    <label for="data1">Pilih Tahapan 1:</label>
    <select name="data1" id="data1" class="form-select">
        <option value="">Pilih tahapan</option>
        @foreach($data1 as $tahapan1)
            <option value="{{ $tahapan1->id }}" {{ request('data1') == $tahapan1->id ? 'selected' : '' }}>
                {{ $tahapan1->id }} - {{ $tahapan1->name }}
            </option>
        @endforeach
    </select>
    </div>
     <div class="col-md-6">
    <label for="data1">Pilih Tahapan 2:</label>
     <select name="data2" id="data2" class="form-select">
        <option value="">Pilih tahapan</option>
        @foreach($data2 as $tahapan2)
            <option value="{{ $tahapan2->id }}" {{ request('data2') == $tahapan2->id ? 'selected' : '' }}>
                {{ $tahapan2->id }} - {{ $tahapan2->name }}
            </option>
        @endforeach
    </select>
    </div>
    </div>
    <button type="submit" class="mt-2 btn btn-primary">Filter</button>
</form>

<div class="table-container">
    <table id="rekapTable" class="table table-striped table-bordered">
        <thead class="table-dark">
            <tr>
                <th class="text-center">No</th>
                <th>Kode SKPD</th>
                <th>Nama OPD</th>
                <th>Sub Unit</th>
                <th>Kode Sub Kegiatan</th>
                <th>Nama Sub Kegiatan</th>
                <th class="text-end">
               
        {{ $data1->firstWhere('id', request('data1'))->name ?? 'N/A' }}
       
           
       
        </th>
                <th class="text-end">
               
          
              {{ $data2->firstWhere('id', request('data2'))->name ?? 'N/A' }}
           
      
        </th>
                <th class="text-end">Selisih</th>
                <th class="text-end">Persentase (%)</th>
            </tr>
        </thead>
        <tbody>
            @foreach($rekap as $index => $row)
            <tr>
                <td class="text-center">{{ $index + 1 }}</td>
                <td>{{ $row->kode_opd }}</td>
                <td>{{ $row->nama_opd }}</td>
                <td>{{ $row->nama_sub_unit }}</td>
                <td>{{ $row->kode_sub_kegiatan }}</td>
                <td>{{ $row->nama_sub_kegiatan }}</td>
                <td class="text-end pagu-original">{{ number_format($row->pagu_original, 2, ',', '.') }}</td>
                <td class="text-end pagu-revisi">{{ number_format($row->pagu_revisi, 2, ',', '.') }}</td>
                <td class="text-end pagu-selisih">{{ number_format($row->selisih, 2, ',', '.') }}</td>
                <td class="text-end">{{ number_format($row->persentase, 2, ',', '.') }}%</td>
            </tr>
            @endforeach
        </tbody>
        <tfoot class="table-dark">
            <tr>
                <th colspan="6" class="text-end">Total:</th>
                <th class="text-end" id="totalPaguOriginal">0</th>
                <th class="text-end" id="totalPaguRevisi">0</th>
                <th class="text-end" id="totalSelisih">0</th>
                <th class="text-end" id="totalPersentase">0%</th>
            </tr>
        </tfoot>
    </table>
</div>

<div class="total-container">
    <span>Total Pagu Original: <strong id="totalOriginalFooter">0</strong></span> |
    <span>Total Pagu Revisi: <strong id="totalRevisiFooter">0</strong></span> |
    <span>Total Selisih: <strong id="totalSelisihFooter">0</strong></span> |
    <span>Persentase Selisih: <strong id="totalPersentaseFooter">0%</strong></span>
</div>

</div>
</div>


<script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.3.6/js/dataTables.buttons.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.3.6/js/buttons.html5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.3.6/js/buttons.print.min.js"></script>

<script>
    $(document).ready(function() {
        var table = $('#rekapTable').DataTable({
            paging: false, // Semua data tampil tanpa pagination
            searching: true,
            ordering: true,
            info: true,
            columnDefs: [{ targets: 0, searchable: false, orderable: false }],
            dom: 'Blfrtip',
            buttons: [
                {
                    extend: 'excelHtml5',
                    text: '📊 Download Excel',
                    className: 'btn btn-success',
                    footer: true,
                    exportOptions: {
                        columns: ':visible',
                        modifier: { page: 'all' },
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
                    text: '📄 Download PDF',
                    className: 'btn btn-danger',
                    orientation: 'landscape',
                    pageSize: 'A4',
                    footer: true,
                    exportOptions: { columns: ':visible', modifier: { page: 'all' } },
                    customize: function(doc) {
                        doc.content[1].table.body.push([
                            { text: "Total", bold: true, alignment: "right", colSpan: 5 }, {}, {}, {}, {},
                            { text: $('#totalPaguOriginal').text(), bold: true },
                            { text: $('#totalPaguRevisi').text(), bold: true },
                            { text: $('#totalSelisih').text(), bold: true },
                            { text: $('#totalPersentase').text(), bold: true }
                        ]);
                    }
                }
            ],
            drawCallback: function() {
                updateTotal();
                table.column(0).nodes().each(function(cell, i) { cell.innerHTML = i + 1; });
            }
        });

        function updateTotal() {
            var totalOriginal = 0, totalRevisi = 0, totalSelisih = 0, totalPersentase = 0;
            var validPersentaseCount = 0;

            $('#rekapTable tbody tr').each(function() {
                var paguOriginal = parseFloat($(this).find('.pagu-original').text().replace(/\./g, '').replace(',', '.')) || 0;
                var paguRevisi = parseFloat($(this).find('.pagu-revisi').text().replace(/\./g, '').replace(',', '.')) || 0;
                var paguSelisih = paguRevisi - paguOriginal;

                totalOriginal += paguOriginal;
                totalRevisi += paguRevisi;
                totalSelisih += paguSelisih;

                // Hitung persentase hanya jika pagu original lebih dari 0
                if (paguOriginal > 0) {
                    var persentase = (paguSelisih / paguOriginal) * 100;
                    totalPersentase += persentase;
                    validPersentaseCount++;
                }
            });

            // Hitung rata-rata persentase selisih
            var avgPersentase = validPersentaseCount > 0 ? (totalPersentase / validPersentaseCount) : 0;

            $('#totalPaguOriginal, #totalOriginalFooter').text(totalOriginal.toLocaleString('id-ID', { minimumFractionDigits: 2 }));
            $('#totalPaguRevisi, #totalRevisiFooter').text(totalRevisi.toLocaleString('id-ID', { minimumFractionDigits: 2 }));
            $('#totalSelisih, #totalSelisihFooter').text(totalSelisih.toLocaleString('id-ID', { minimumFractionDigits: 2 }));
            $('#totalPersentase, #totalPersentaseFooter').text(avgPersentase.toLocaleString('id-ID', { minimumFractionDigits: 2 }) + '%');
        }
    });
</script>

@endsection
