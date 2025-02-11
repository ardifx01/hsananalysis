@extends('layouts.app')

@section('title', 'Rekap Pagu Per OPD')
@section('page-title', 'Rekapitulasi Pagu Per OPD')

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
</style>

<div class="container">

    <!-- Tabel Data -->
    <div class="table-responsive">
        <table id="rekapTable" class="table table-striped table-bordered table-sm">
            <thead class="table-dark">
                <tr>
                    <th>No</th>
                    <th>Kode OPD</th>
                    <th>Nama OPD</th>
                    <th>Pagu Original</th>
                    <th>Persentase Penyesuaian</th>
                    <th>Nilai Penyesuaian</th>
                    <th>Pagu Setelah Penyesuaian</th>
                </tr>
            </thead>
            <tbody>
                @php
                    $totalPaguOriginal = 0;
                    $totalNilaiPenyesuaian = 0;
                    $totalPaguSetelah = 0;
                @endphp

                @foreach($data as $index => $row)
                    @php
                        $totalPaguOriginal += $row->pagu_original;
                        $totalNilaiPenyesuaian += $row->nilai_penyesuaian;
                        $totalPaguSetelah += $row->pagu_setelah_penyesuaian;
                    @endphp
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td>{{ $row->kode_skpd }}</td>
                        <td>{{ $row->nama_skpd }}</td>
                        <td class="text-end">{{ number_format($row->pagu_original, 0, ',', '.') }}</td>
                        <td class="text-end">{{ number_format($row->persentase_penyesuaian, 2, ',', '.') }}%</td>
                        <td class="text-end">{{ number_format($row->nilai_penyesuaian, 0, ',', '.') }}</td>
                        <td class="text-end">{{ number_format($row->pagu_setelah_penyesuaian, 0, ',', '.') }}</td>
                    </tr>
                @endforeach
            </tbody>
            <tfoot class="table-dark">
                <tr>
                    <th colspan="3" class="text-end">Total:</th>
                    <th class="text-end">{{ number_format($totalPaguOriginal, 0, ',', '.') }}</th>
                    <th class="text-end">
                        {{ number_format(($totalPaguOriginal > 0 ? ($totalNilaiPenyesuaian / $totalPaguOriginal * 100) : 0), 2, ',', '.') }}%
                    </th>
                    <th class="text-end">{{ number_format($totalNilaiPenyesuaian, 0, ',', '.') }}</th>
                    <th class="text-end">{{ number_format($totalPaguSetelah, 0, ',', '.') }}</th>
                </tr>
            </tfoot>
        </table>
    </div>
</div>

<!-- jQuery untuk DataTables & Export -->
<script>
    $(document).ready(function() {
        $('#rekapTable').DataTable({
            dom: 'Bfrtip',
            buttons: [
                { extend: 'copy', text: 'Copy', className: 'btn btn-secondary' },
                { extend: 'csv', text: 'CSV', className: 'btn btn-info' },
                { extend: 'excel', text: 'Excel', className: 'btn btn-success' },
                { extend: 'pdf', text: 'PDF', className: 'btn btn-danger', orientation: 'landscape' },
                { extend: 'print', text: 'Print', className: 'btn btn-primary' }
            ],
            paging: false,
            searching: true,
            responsive: true
        });
    });
</script>

@endsection
