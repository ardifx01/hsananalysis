@extends('layouts.app')

@section('title', 'Rekap Sub Kegiatan & Rekening Per OPD')
@section('page-title', 'Rekap Sub Kegiatan & Rekening Per OPD')

@section('content')

<!-- Import DataTables & jQuery -->
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css">
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>

<style>
    /* Tabel utama */
    .table-sm th, .table-sm td {
        padding: 4px 8px;
        font-size: 11px;
        vertical-align: middle;
    }
    .wrap-text {
        white-space: normal !important;
        word-wrap: break-word;
        overflow-wrap: break-word;
    }
    .col-subkeg { width: 12%; }
    .col-nama-subkeg { width: 20%; }
    .col-pagu-subkeg { width: 10%; }
    .col-koderek { width: 12%; }
    .col-nama-rek { width: 20%; }
    .col-pagu-rek { width: 10%; }
    .col-persentase { width: 8%; }
    .col-pagu-pengurangan { width: 8%; }
    .col-pagu-setelah { width: 10%; }

    .table-dark {
        background-color: #2c3e50; 
        color: #fff;
    }
    tfoot.table-dark th {
        background-color: #2c3e50;
        color: #fff;
    }

    /* Tabel rekap BPD lebih gelap & compact */
    .rekap-bpd-table thead {
        background-color: #343a40; 
        color: #fff;
    }
    .rekap-bpd-table th,
    .rekap-bpd-table td {
        padding: 4px 8px;
        font-size: 11px;
        vertical-align: middle;
    }
</style>

<div class="container">

    <!-- Filter OPD -->
    <form id="filter-form" class="row g-3 mb-3">
        <div class="col-md-4">
            <label for="kode_opd" class="form-label">Pilih OPD</label>
            <select name="kode_opd" id="kode_opd" class="form-select form-select-sm">
                <option value="">Silakan pilih OPD</option>
                @foreach($opds as $opd)
                    <option value="{{ $opd->kode_skpd }}">{{ $opd->kode_skpd }} - {{ $opd->nama_skpd }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-4 d-flex align-items-end">
            <button type="button" id="filter-btn" class="btn btn-primary w-100 btn-sm">Filter</button>
            <button type="reset" id="reset-filter" class="btn btn-secondary w-100 ms-2 btn-sm">Reset</button>
        </div>
    </form>

    <!-- Tabel Utama -->
    <div class="table-responsive">
        <table id="subkeg_table" class="table table-striped table-bordered table-sm">
            <thead class="table-dark text-center">
                <tr>
                    <th rowspan="2" class="col-subkeg">Kode Sub Kegiatan</th>
                    <th rowspan="2" class="col-nama-subkeg">Nama Sub Kegiatan</th>
                    <th rowspan="2" class="col-pagu-subkeg">Pagu Murni</th>
                    <th colspan="3">Rekening</th>
                    <th rowspan="2" class="col-persentase">Persentase</th>
                    <th rowspan="2" class="col-pagu-pengurangan">Pagu Pengurangan</th>
                    <th rowspan="2" class="col-pagu-setelah">Pagu Setelah Pengurangan</th>
                </tr>
                <tr>
                    <th class="col-koderek">Kode Rekening</th>
                    <th class="col-nama-rek">Nama Rekening</th>
                    <th class="col-pagu-rek">Pagu Rekening</th>
                </tr>
            </thead>
            <tbody></tbody>
            <tfoot class="table-dark">
                <tr>
                    <th colspan="2" class="text-end">Total Pagu Sub Keg</th>
                    <th id="total-pagu-subkeg" class="text-end"></th>
                    <th colspan="2" class="text-end">Total Pagu Rekening</th>
                    <th id="total-pagu-rekening" class="text-end"></th>
                    <th></th>
                    <th id="total-pagu-pengurangan" class="text-end"></th>
                    <th id="total-pagu-setelah" class="text-end"></th>
                </tr>
            </tfoot>
        </table>
    </div>

    <!-- Tabel Rekap BPD (dengan kolom Pagu Setelah) -->
    <div id="rekapBpdContainer" style="margin-top: 30px;">
        <h5 id="rekapBpdTitle" style="font-weight: bold;">Rekap BPD</h5>
        <table id="rekap_bpd_table" class="table table-bordered table-sm rekap-bpd-table">
            <thead class="text-center">
                <tr>
                    <th>Kode Rekening (BPD)</th>
                    <th>Nama Rekening</th>
                    <th>Pagu Murni</th>
                    <th>Pagu Setelah Pengurangan</th>
                </tr>
            </thead>
            <tbody></tbody>
            <tfoot>
                <tr>
                    <th colspan="2" class="text-end">Total</th>
                    <th id="rekap_bpd_total" class="text-end">0</th>
                    <th id="rekap_bpd_total_setelah" class="text-end">0</th>
                </tr>
            </tfoot>
        </table>
    </div>
</div>

<script>
$(document).ready(function() {

    // =========== Tabel Utama ===========
    let table = $('#subkeg_table').DataTable({
        paging: false,
        searching: true,
        info: false,
        ordering: false,
        processing: true,
        serverSide: false,
        columns: [
            { data: 'kode_sub_kegiatan', className: 'small text-center wrap-text col-subkeg' },
            { data: 'nama_sub_kegiatan', className: 'small wrap-text col-nama-subkeg' },
            { data: 'pagu_murni', className: 'small text-end col-pagu-subkeg' },
            { data: 'kode_rekening', className: 'small text-center wrap-text col-koderek' },
            { data: 'nama_rekening', className: 'small wrap-text col-nama-rek' },
            { data: 'pagu', className: 'small text-end col-pagu-rek' },
            {
                data: null,
                className: 'small text-center col-persentase',
                render: function(data, type, row, meta) {
                    if (
                        row.nama_rekening !== '-' &&
                        row.nama_rekening.toLowerCase().includes('belanja perjalanan dinas')
                    ) {
                        return `
                            <div style="display: flex; flex-direction: column; align-items: center;">
                                <input type="range" min="0" max="100" value="0" step="1"
                                    class="form-range persentase-slider"
                                    style="width: 100px;">
                                <span class="persentase-label">0%</span>
                            </div>
                        `;
                    } else {
                        return '-';
                    }
                }
            },
            {
                data: null,
                className: 'small text-end col-pagu-pengurangan',
                render: function(data, type, row, meta) {
                    if (
                        row.nama_rekening !== '-' &&
                        row.nama_rekening.toLowerCase().includes('belanja perjalanan dinas')
                    ) {
                        return '0'; // default
                    } else {
                        return '-';
                    }
                }
            },
            {
                data: null,
                className: 'small text-end col-pagu-setelah',
                render: function(data, type, row, meta) {
                    if (
                        row.nama_rekening !== '-' &&
                        row.nama_rekening.toLowerCase().includes('belanja perjalanan dinas')
                    ) {
                        // Nilai awal = Pagu Rekening (belum ada pengurangan)
                        let paguStr = row.pagu || '0';
                        let pagu = parseInt(paguStr.replace(/\./g, '')) || 0;
                        return new Intl.NumberFormat('id-ID').format(pagu);
                    } else {
                        return '-';
                    }
                }
            }
        ],
        footerCallback: function(row, data, start, end, display) {
            let totalSubkeg = 0;
            let totalRek = 0;

            data.forEach(function(item) {
                if (item.pagu_murni && item.pagu_murni !== '') {
                    let valSubkeg = parseInt(item.pagu_murni.replace(/\./g, '')) || 0;
                    totalSubkeg += valSubkeg;
                }
                if (item.pagu && item.pagu !== '-') {
                    let valRek = parseInt(item.pagu.replace(/\./g, '')) || 0;
                    totalRek += valRek;
                }
            });

            $('#total-pagu-subkeg').text(new Intl.NumberFormat('id-ID').format(totalSubkeg));
            $('#total-pagu-rekening').text(new Intl.NumberFormat('id-ID').format(totalRek));
        }
    });

    // =========== Load Rekap BPD ===========
    function loadRekapBpd(kodeOpd) {
        $.ajax({
            url: "{{ route('simulasi.get-rekap-bpd-by-opd') }}",
            type: "GET",
            data: { kode_opd: kodeOpd },
            dataType: "json",
            success: function(rekap) {
                let opdText = $("#kode_opd option:selected").text();
                $("#rekapBpdTitle").text("Rekap BPD untuk " + opdText);

                let totalPagu = 0;
                let totalSetelah = 0;
                let html = "";

                rekap.forEach(function(item) {
                    let paguMurni = parseInt(item.total_pagu) || 0;
                    totalPagu += paguMurni;

                    // sum "pagu setelah" di tabel utama
                    let sumSetelah = sumPaguSetelahByRekening(item.kode_rekening);

                    // jika sumSetelah == 0, maka belum ada pengurangan => set sumSetelah = paguMurni
                    if (sumSetelah === 0) {
                        sumSetelah = paguMurni;
                    }
                    totalSetelah += sumSetelah;

                    let paguMurniFormatted = new Intl.NumberFormat('id-ID').format(paguMurni);
                    let setelahFormatted = new Intl.NumberFormat('id-ID').format(sumSetelah);

                    html += `
                        <tr>
                            <td>${item.kode_rekening}</td>
                            <td>${item.nama_rekening}</td>
                            <td class="text-end">${paguMurniFormatted}</td>
                            <td class="text-end">${setelahFormatted}</td>
                        </tr>
                    `;
                });

                $("#rekap_bpd_table tbody").html(html);

                $("#rekap_bpd_total").text(
                    new Intl.NumberFormat('id-ID').format(totalPagu)
                );
                $("#rekap_bpd_total_setelah").text(
                    new Intl.NumberFormat('id-ID').format(totalSetelah)
                );
            },
            error: function(xhr, status, error) {
                console.error("Error rekap BPD:", error);
                $("#rekapBpdTitle").text("Rekap BPD");
                $("#rekap_bpd_table tbody").html("");
                $("#rekap_bpd_total").text("0");
                $("#rekap_bpd_total_setelah").text("0");
            }
        });
    }

    // Fungsi untuk menjumlah "pagu setelah" di tabel utama (kolom 9) 
    // berdasarkan code_rekening
    function sumPaguSetelahByRekening(kodeRek) {
        let sum = 0;
        table.rows().every(function() {
            let rowData = this.data() || {};
            if (rowData.kode_rekening === kodeRek) {
                let rowNode = this.node();
                let col9Text = $(rowNode).find('td.col-pagu-setelah').text().replace(/\./g, '');
                let valSetelah = parseInt(col9Text) || 0;
                sum += valSetelah;
            }
        });
        return sum;
    }

    // =========== Event Filter ===========

    $('#filter-btn').on('click', function() {
        let kodeOpd = $('#kode_opd').val();
        if (kodeOpd) {
            // 1) Tabel utama
            $.ajax({
                url: "{{ route('simulasi.get-subkeg-by-opd') }}",
                type: "GET",
                data: { kode_opd: kodeOpd },
                dataType: "json",
                success: function(response) {
                    table.clear().rows.add(response).draw();
                    recalcPenguranganSetelah();
                },
                error: function(xhr, status, error) {
                    console.error("Error subkeg:", error);
                }
            });

            // 2) Rekap BPD
            loadRekapBpd(kodeOpd);

        } else {
            table.clear().draw();
            $("#rekapBpdTitle").text("Rekap BPD");
            $("#rekap_bpd_table tbody").html("");
            $("#rekap_bpd_total").text("0");
            $("#rekap_bpd_total_setelah").text("0");
        }
    });

    // Reset
    $('#reset-filter').on('click', function() {
        $('#kode_opd').val("");
        table.clear().draw();
        $("#rekapBpdTitle").text("Rekap BPD");
        $("#rekap_bpd_table tbody").html("");
        $("#rekap_bpd_total").text("0");
        $("#rekap_bpd_total_setelah").text("0");
    });

    // =========== Recalc Pengurangan & Setelah di Footer ===========

    function recalcPenguranganSetelah() {
        let totalPengurangan = 0;
        let totalSetelah = 0;

        table.rows().every(function() {
            let rowNode = this.node();
            let col8Text = $(rowNode).find('td.col-pagu-pengurangan').text().replace(/\./g, '');
            let col9Text = $(rowNode).find('td.col-pagu-setelah').text().replace(/\./g, '');

            let valPengurangan = parseInt(col8Text) || 0;
            let valSetelah = parseInt(col9Text) || 0;

            totalPengurangan += valPengurangan;
            totalSetelah += valSetelah;
        });

        $('#total-pagu-pengurangan').text(
            new Intl.NumberFormat('id-ID').format(totalPengurangan)
        );
        $('#total-pagu-setelah').text(
            new Intl.NumberFormat('id-ID').format(totalSetelah)
        );
    }

    // Event slider => update col 8 & 9 + update rekap BPD
    $('#subkeg_table tbody').on('input', '.persentase-slider', function() {
        let val = parseFloat($(this).val()) || 0;
        $(this).closest('div').find('.persentase-label').text(val + '%');

        let tr = $(this).closest('tr');
        let rowData = table.row(tr).data() || {};

        let paguStr = rowData.pagu || '0';
        let pagu = parseInt(paguStr.replace(/\./g, '')) || 0;

        let pengurangan = Math.round(pagu * val / 100);
        let setelah = pagu - pengurangan;

        tr.find('td.col-pagu-pengurangan').text(
            new Intl.NumberFormat('id-ID').format(pengurangan)
        );
        tr.find('td.col-pagu-setelah').text(
            new Intl.NumberFormat('id-ID').format(setelah)
        );

        recalcPenguranganSetelah();

        // Update rekap BPD (kolom Pagu Setelah) 
        let kodeOpd = $('#kode_opd').val();
        if (kodeOpd) {
            loadRekapBpd(kodeOpd);
        }
    });
});
</script>
@endsection
