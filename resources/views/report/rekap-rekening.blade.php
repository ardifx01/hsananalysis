@extends('layouts.app')

@section('title', 'Rekap Kode Rekening')
@section('page-title', 'Rekapitulasi Kode Rekening')

@section('content')

<!-- Tambahkan CDN DataTables -->
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css">
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.3.6/js/dataTables.buttons.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.3.6/js/buttons.html5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.3.6/js/buttons.print.min.js"></script>

<div class="container">

    <!-- Tabel DataTables -->
    <div class="table-responsive">
        <table id="rekapTable" class="table table-striped table-bordered table-sm">
            <thead class="table-dark">
                <tr>
                    <th style="width: 5%;">No</th>
                    <th style="width: 15%;">Kode Rekening</th>
                    <th style="width: 25%;">Nama Rekening</th>
                    <th style="width: 15%;">Pagu Original</th>
                    <th style="width: 15%;">Pagu Revisi</th>
                    <th style="width: 15%;">Selisih</th>
                    <th style="width: 10%;">% Perubahan</th>
                </tr>
            </thead>
            <tbody></tbody>
            <tfoot>
                <tr>
                    <th colspan="3" class="text-end">Total:</th>
                    <th id="total-pagu-original">0</th>
                    <th id="total-pagu-revisi">0</th>
                    <th id="total-pagu-selisih">0</th>
                    <th id="total-pagu-persentase">0%</th>
                </tr>
            </tfoot>
        </table>
    </div>
</div>

<script>
    $(document).ready(function() {
        var table = $('#rekapTable').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: "{{ route('rekap.rekening.data') }}",
                dataSrc: 'data'
            },
            columns: [
                { data: null, render: function(data, type, row, meta) { return meta.row + 1; }, orderable: false, searchable: false },
                { data: 'kode_rekening', name: 'kode_rekening' },
                { data: 'nama_rekening', name: 'nama_rekening' },
                { data: 'pagu_original', name: 'pagu_original', render: function(data) { return formatRupiah(data); } },
                { data: 'pagu_revisi', name: 'pagu_revisi', render: function(data) { return formatRupiah(data); } },
                { 
                    data: null, 
                    render: function(data) {
                        let selisih = data.pagu_revisi - data.pagu_original;
                        return formatRupiah(selisih);
                    }
                },
                { 
                    data: null, 
                    render: function(data) {
                        if (data.pagu_original > 0) {
                            let persen = ((data.pagu_revisi - data.pagu_original) / data.pagu_original * 100).toFixed(2);
                            return persen + "%";
                        }
                        return "0%";
                    }
                }
            ],
            dom: 'Bfrtip',
            buttons: [
                { extend: 'excelHtml5', text: 'Export Excel', className: 'btn btn-success' },
                { extend: 'pdfHtml5', text: 'Export PDF', className: 'btn btn-danger', orientation: 'landscape' },
                { extend: 'print', text: 'Print', className: 'btn btn-primary' }
            ],
            drawCallback: function() {
                let totalOriginal = 0, totalRevisi = 0, totalSelisih = 0;
                
                this.api().rows({ search: 'applied' }).every(function() {
                    let data = this.data();
                    totalOriginal += parseFloat(data.pagu_original);
                    totalRevisi += parseFloat(data.pagu_revisi);
                    totalSelisih += (parseFloat(data.pagu_revisi) - parseFloat(data.pagu_original));
                });

                $('#total-pagu-original').text(formatRupiah(totalOriginal));
                $('#total-pagu-revisi').text(formatRupiah(totalRevisi));
                $('#total-pagu-selisih').text(formatRupiah(totalSelisih));
                $('#total-pagu-persentase').text(totalOriginal > 0 ? ((totalSelisih / totalOriginal) * 100).toFixed(2) + "%" : "0%");
            }
        });

        function formatRupiah(angka) {
            return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(angka);
        }
    });
</script>

@endsection
