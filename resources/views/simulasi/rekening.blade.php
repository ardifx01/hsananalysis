@extends('layouts.app')

@section('title', 'Rekap Per Rekening')
@section('page-title', 'Rekapitulasi Per Rekening')

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
    .btn-container { margin-bottom: 10px; }


     

        td.nama-rekening {
            max-width: 250px;
            /* Tentukan ukuran tetap */

            white-space: normal;
            /* Memungkinkan wrap text */

        }
</style>

<div class="container">
    <div class="table-responsive">
        <table id="rekapTable" class="table table-striped table-bordered table-sm">
            <thead class="table-dark">
                <tr>
                    <th>No</th>
                    <th>Kode Rekening</th>
                    <th>Nama Rekening</th>
                    <th>Pagu Original</th>
                    <th>Persentase Penyesuaian</th>
                    <th>Nilai Penyesuaian</th>
                    <th>Pagu Setelah Penyesuaian</th>
                </tr>
            </thead>
            <tbody>
                @php
                    $total_pagu_original = 0;
                    $total_nilai_penyesuaian = 0;
                    $total_pagu_setelah = 0;
                @endphp
                @foreach($data as $index => $row)
                    @php
                        $total_pagu_original += $row->pagu_original;
                        $total_nilai_penyesuaian += $row->nilai_penyesuaian_total;
                        $total_pagu_setelah += $row->pagu_setelah_penyesuaian;
                    @endphp
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td>{{ $row->kode_rekening }}</td>
                        <td class="nama-rekening">{{ $row->nama_rekening}}</td>
                        <td class="text-end">{{ number_format($row->pagu_original, 0, ',', '.') }}</td>
                        <td class="text-end">{{ number_format($row->persentase_akhir, 2, ',', '.') }}%</td>
                        <td class="text-end">{{ number_format($row->nilai_penyesuaian_total, 0, ',', '.') }}</td>
                        <td class="text-end">{{ number_format($row->pagu_setelah_penyesuaian, 0, ',', '.') }}</td>
                    </tr>
                @endforeach
            </tbody>
            <tfoot class="table-dark">
    <tr>
        <th colspan="3" class="text-end">Total:</th>
        <th class="text-end">{{ number_format($total_pagu_original, 0, ',', '.') }}</th>
        <th class="text-end" id="total-persentase">0%</th>
        <th class="text-end">{{ number_format($total_nilai_penyesuaian, 0, ',', '.') }}</th>
        <th class="text-end">{{ number_format($total_pagu_setelah, 0, ',', '.') }}</th>
    </tr>
</tfoot>

        </table>
    </div>
</div>

<!-- jQuery untuk DataTables -->
<script>
    $(document).ready(function() {
        let table = $('#rekapTable').DataTable({
            dom: 'Bfrtip',
            buttons: [
                {
                    extend: 'excelHtml5',
                    text: 'Export Excel',
                    className: 'btn btn-success',
                    exportOptions: {
                        columns: ':visible',
                        format: {
                            body: function (data, row, column, node) {
                                // Hilangkan pemisah ribuan (.) sebelum diekspor
                                return data.replace(/\./g, '').replace(/,/g, '.');
                            }
                        }
                    }
                },
                { extend: 'pdfHtml5', text: 'Export PDF', className: 'btn btn-danger', orientation: 'landscape' },
                { extend: 'print', text: 'Print', className: 'btn btn-primary' }
            ],
            paging: false,
            searching: true,
            responsive: true
        });

        $('#export-excel').on('click', function() {
            table.button('.buttons-excel').trigger();
        });

        $('#export-pdf').on('click', function() {
            table.button('.buttons-pdf').trigger();
        });

        $('#export-print').on('click', function() {
            table.button('.buttons-print').trigger();
        });
    });


    function hitungTotalPersentase() {
    let totalPersentase = 0;
    let jumlahBaris = 0;

    $('#rekapTable tbody tr').each(function() {
        let persenText = $(this).find('td:eq(4)').text().replace('%', '').replace(',', '.').trim();
        let persen = parseFloat(persenText);

        if (!isNaN(persen)) {
            totalPersentase += persen;
            jumlahBaris++;
        }
    });

    let rataPersentase = jumlahBaris > 0 ? (totalPersentase / jumlahBaris).toFixed(2).replace('.', ',') : "0,00";
    $('#total-persentase').text(rataPersentase + "%");
}

// Panggil fungsi saat halaman selesai dimuat
hitungTotalPersentase();
</script>

@endsection
