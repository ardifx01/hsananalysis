@extends('layouts.app')

@section('title', 'Progress Pergeseran')
@section('page-title', 'Progress Pergeseran')

@section('content')
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/jquery.dataTables.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.3.6/css/buttons.dataTables.min.css">
<style>
    .table-sm th, .table-sm td { padding: 4px 8px; font-size: 12px; }
</style>

<div class="container">
    <div class="table-responsive">
        <table id="progressTable" class="table table-striped table-bordered table-sm">
            <thead class="table-dark">
                <tr>
                    <th>No</th>
                    <th>Kode OPD</th>
                    <th>Nama OPD</th>
                    <th>Pagu Murni</th>
                    <th>Efesiensi</th>
                    <th>Pagu Efesiensi</th>
                    <th>Pergeseran</th>
                    <th>Murni-Pergeseran</th> <!-- Kolom yang akan disembunyikan -->
                    <th>Target Efesiensi</th>
                </tr>
            </thead>
            <tbody>
                @foreach($data as $index => $row)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td>{{ $row->kode_skpd }}</td>
                        <td>{{ $row->nama_skpd }}</td>
                        <td class="text-end">{{ number_format($row->pagu_original, 0, ',', '.') }}</td>
                        <td class="text-end">{{ number_format($row->nilai_penyesuaian, 0, ',', '.') }}</td>
                        <td class="text-end">{{ number_format($row->pagu_setelah_penyesuaian, 0, ',', '.') }}</td>
                        <td class="text-end">{{ number_format($row->pagu_revisi, 0, ',', '.') }}</td>
                        <td class="text-end">{{ number_format($row->pagu_revisi - $row->pagu_original, 0, ',', '.') }}</td> <!-- Kolom yang akan disembunyikan -->
                        <td class="text-end" style="color: {{ ($row->pagu_setelah_penyesuaian - $row->pagu_revisi) < 0 ? 'red' : 'green' }};">
                            {{ number_format($row->pagu_setelah_penyesuaian - $row->pagu_revisi, 0, ',', '.') }}
                        </td>
                    </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr>
                    <th colspan="3" class="text-end">Total</th>
                    <th class="text-end"></th>
                    <th class="text-end"></th>
                    <th class="text-end"></th>
                    <th class="text-end"></th>
                    <th class="text-end"></th> <!-- Kolom yang akan disembunyikan -->
                    <th class="text-end"></th>
                </tr>
            </tfoot>
        </table>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.3.6/js/dataTables.buttons.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.3.6/js/buttons.html5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.3.6/js/buttons.print.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.36/pdfmake.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.36/vfs_fonts.js"></script>

<script>
    $(document).ready(function() {
        var table = $('#progressTable').DataTable({
            dom: 'Bfrtip',
            buttons: [
                'copy', 'csv', 'excel', 'pdf', 'print'
            ],
            paging: false,
            searching: true,
            ordering: true,
            info: true,
            responsive: true,
            lengthMenu: [[10, 25, 50, -1], [10, 25, 50, "All"]],
            pageLength: 10,
            language: {
                search: "Cari:",
                lengthMenu: "Tampilkan _MENU_ data per halaman",
                zeroRecords: "Tidak ada data yang ditemukan",
                info: "Menampilkan _START_ sampai _END_ dari _TOTAL_ data",
                infoEmpty: "Tidak ada data yang tersedia",
                infoFiltered: "(difilter dari total _MAX_ data)",
                paginate: {
                    first: "Pertama",
                    last: "Terakhir",
                    next: "Berikutnya",
                    previous: "Sebelumnya"
                }
            },
            columnDefs: [
                {
                    targets: [4], // Kolom yang akan disembunyikan
                    visible: false,
                    searchable: false
                }
            ],
            footerCallback: function (row, data, start, end, display) {
                var api = this.api();

                // Remove the formatting to get integer data for summation
                var intVal = function (i) {
                    return typeof i === 'string' ?
                        parseFloat(i.replace(/[\.,]/g, '')) || 0 :
                        typeof i === 'number' ?
                            i : 0;
                };

                // Total over all pages
                totalPaguOriginal = api
                    .column(3)
                    .data()
                    .reduce(function (a, b) {
                        return intVal(a) + intVal(b);
                    }, 0);

                totalNilaiPenyesuaian = api
                    .column(4)
                    .data()
                    .reduce(function (a, b) {
                        return intVal(a) + intVal(b);
                    }, 0);

                totalPaguSetelahPenyesuaian = api
                    .column(5)
                    .data()
                    .reduce(function (a, b) {
                        return intVal(a) + intVal(b);
                    }, 0);

                totalPaguRevisi = api
                    .column(6)
                    .data()
                    .reduce(function (a, b) {
                        return intVal(a) + intVal(b);
                    }, 0);

                totalSelisih = api
                    .column(7)
                    .data()
                    .reduce(function (a, b) {
                        return intVal(a) + intVal(b);
                    }, 0);

                // Update footer
                $(api.column(3).footer()).html(
                    totalPaguOriginal.toLocaleString('id-ID')
                );
                $(api.column(4).footer()).html(
                    totalNilaiPenyesuaian.toLocaleString('id-ID')
                );
                $(api.column(5).footer()).html(
                    totalPaguSetelahPenyesuaian.toLocaleString('id-ID')
                );
                $(api.column(6).footer()).html(
                    totalPaguRevisi.toLocaleString('id-ID')
                );
                $(api.column(7).footer()).html(
                    totalSelisih.toLocaleString('id-ID')
                );
            }
        });
    });
</script>
@endsection