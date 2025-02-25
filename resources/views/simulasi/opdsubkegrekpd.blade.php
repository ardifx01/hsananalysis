@extends('layouts.app')

@section('title', 'Rekap Sub Kegiatan & Rekening Per OPD')
@section('page-title', 'Rekap Sub Kegiatan & Rekening Per OPD')

@section('content')

<!-- (1) Import DataTables & Buttons CSS/JS -->
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
    /* Tabel Utama */
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
    .col-persentase { width: 12%; }
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

    /* Tabel rekap BPD di bawah */
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

    /* Input manual kolom 8 */
    .pagu-pengurangan-input {
        width: 70px;
        text-align: right;
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

    <!-- (2) Tabel Utama -->
    <div class="table-responsive mb-3">
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

    <!-- Tombol Konfirmasi -->
    <button id="btn-konfirmasi" class="btn btn-success btn-sm mb-3">Konfirmasi Simpan</button>

    <!-- (3) Tabel Rekap -->
    <h5 id="rekapBpdTitle" style="font-weight: bold;">Rekap Perjalanan Dinas</h5>
    <div class="table-responsive">
        <table id="rekap_bpd_table" class="table table-bordered table-sm rekap-bpd-table">
            <thead class="text-center">
                <tr>
                    <th>Kode Rekening (BPD)</th>
                    <th>Nama Rekening</th>
                    <th>Nama OPD</th>
                    <th>Pagu Murni</th>
                    <th>Pagu Pengurangan</th>
                    <th>Pagu Setelah Pengurangan</th>
                    <th>Persentase</th>
                </tr>
            </thead>
            <tbody></tbody>
            <tfoot>
                <tr>
                    <th colspan="3" class="text-end">Total</th>
                    <th id="rekap_bpd_total" class="text-end">0</th>
                    <th id="rekap_bpd_total_pengurangan" class="text-end">0</th>
                    <th id="rekap_bpd_total_setelah" class="text-end">0</th>
                    <th id="rekap_bpd_total_persentase" class="text-end">0%</th>
                </tr>
            </tfoot>
        </table>
    </div>

</div>

<script>
$(document).ready(function() {

    // (A) DataTables Tabel Utama
    let table = $('#subkeg_table').DataTable({
        dom: 'Bfrtip',
        buttons: [
            // Tombol Export Excel biasa
            {
                extend: 'excelHtml5',
                text: 'Export Excel',
                className: 'btn btn-sm btn-success',
                footer: true,
                title: 'Rekapitulasi Perjalanan Dinas Pada Sub Kegiatan',
                exportOptions: {
                    format: {
                        body: function (data, row, column, node) {
                            // Kolom 6 => Persentase slider
                            if (column === 6) {
                                let sliderVal = $(node).find('.persentase-slider').val() || '0';
                                return sliderVal + '%';
                            }
                            // Kolom 7 => Pagu Pengurangan (input)
                            else if (column === 7) {
                                let inputVal = $(node).find('.pagu-pengurangan-input').val() || '0';
                                return inputVal.replace(/\./g, '');
                            }
                            // Kolom 8 => Pagu Setelah
                            else if (column === 8) {
                                return data.replace(/\./g, '');
                            }
                            return data.replace(/<[^>]*>/g, '');
                        }
                    }
                }
            },
            // Tombol Export PDF biasa (hanya tabel utama)
            {
                extend: 'pdfHtml5',
                text: 'Export PDF',
                className: 'btn btn-sm btn-danger',
                footer: true,
                orientation: 'landscape',
                pageSize: 'A4',
                title: 'Rekapitulasi Perjalanan Dinas Pada Sub Kegiatan',
                exportOptions: {
                    format: {
                        body: function (data, row, column, node) {
                            if (column === 6) {
                                let sliderVal = $(node).find('.persentase-slider').val() || '0';
                                return sliderVal + '%';
                            }
                            else if (column === 7) {
                                let inputVal = $(node).find('.pagu-pengurangan-input').val() || '0';
                                return inputVal;
                            }
                            else if (column === 8) {
                                return data;
                            }
                            return data;
                        }
                    }
                }
            },
            // (B) Tombol Kustom: Export PDF (Keduanya) => Membangun doc.content manual
            {
                extend: 'pdfHtml5',
                text: 'Export PDF (Keduanya)',
                className: 'btn btn-sm btn-warning',
                orientation: 'landscape',
                pageSize: 'A4',
                customize: function(doc) {
                    // Hapus isi default
                    doc.content.splice(0, doc.content.length);

                    // 1) Bangun body Tabel Utama
                    let mainBody = buildTableMainBody();

                    doc.content.push(
                        { text: "Tabel Utama (Sub Kegiatan)", style: 'header', margin: [0,0,0,10] },
                        {
                            table: {
                                headerRows: 1,
                                // Lebar kolom => 9 kolom
                                widths: ['auto','auto','auto','auto','auto','auto','auto','auto','auto'],
                                body: mainBody
                            }
                        }
                    );

                    // 2) Bangun body Tabel Rekap
                    let rekapBody = buildTableRekapBody();

                    doc.content.push(
                        { text: "Tabel Rekap (Perjalanan Dinas)", style: 'header', margin: [0,20,0,10] },
                        {
                            table: {
                                headerRows: 1,
                                // 7 kolom
                                widths: ['auto','auto','auto','auto','auto','auto','auto'],
                                body: rekapBody
                            }
                        }
                    );

                    // Styling
                    doc.styles.header = { fontSize: 12, bold: true };
                    doc.defaultStyle.fontSize = 9;
                },
                exportOptions: {
                    // columns => tidak dipakai, kita override doc.content
                }
            }
        ],
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
                    let valPersen = row.persentase || 0;
                    if (row.nama_rekening !== '-' && row.nama_rekening.toLowerCase().includes('belanja perjalanan dinas')) {
                        return `
                            <div style="display: flex; flex-direction: column; align-items: center;">
                                <input type="range" min="0" max="100" value="${valPersen}" step="1"
                                    class="form-range persentase-slider"
                                    style="width: 100px;">
                                <span class="persentase-label">${valPersen}%</span>
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
                    if (row.nama_rekening && row.nama_rekening.toLowerCase().includes('belanja perjalanan dinas')) {
                        return `<input type="text" class="pagu-pengurangan-input" value="0">`;
                    } else {
                        return '-';
                    }
                }
            },
            {
                data: null,
                className: 'small text-end col-pagu-setelah',
                render: function(data, type, row, meta) {
                    if (row.nama_rekening && row.nama_rekening.toLowerCase().includes('belanja perjalanan dinas')) {
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


    

    // (B) DataTables Tabel Rekap
    let rekapTable = $('#rekap_bpd_table').DataTable({
        dom: 'Bfrtip',
        buttons: [
            {
                extend: 'excelHtml5',
                text: 'Export Excel',
                className: 'btn btn-sm btn-success',
                footer: true,
                title: 'Rekap Perjalanan Dinas'
            },
            {
                extend: 'pdfHtml5',
                text: 'Export PDF',
                className: 'btn btn-sm btn-danger',
                footer: true,
                orientation: 'landscape',
                pageSize: 'A4',
                title: 'Rekap Perjalanan Dinas'
            }
        ],
        paging: false,
        searching: false,
        info: false,
        ordering: false,
        columns: [
            { data: 'kode_rekening' },
            { data: 'nama_rekening' },
            { data: 'nama_opd' },
            { data: 'pagu_murni', className: 'text-end' },
            { data: 'pagu_pengurangan', className: 'text-end' },
            { data: 'pagu_setelah', className: 'text-end' },
            { data: 'persentase', className: 'text-end' }
        ],
        footerCallback: function(row, data, start, end, display) {
            let totalMurni = 0;
            let totalPengurangan = 0;
            let totalSetelah = 0;

            data.forEach(function(item) {
                totalMurni += parseInt(item.pagu_murni || 0);
                totalPengurangan += parseInt(item.pagu_pengurangan || 0);
                totalSetelah += parseInt(item.pagu_setelah || 0);
            });

            $('#rekap_bpd_total').text(new Intl.NumberFormat('id-ID').format(totalMurni));
            $('#rekap_bpd_total_pengurangan').text(new Intl.NumberFormat('id-ID').format(totalPengurangan));
            $('#rekap_bpd_total_setelah').text(new Intl.NumberFormat('id-ID').format(totalSetelah));

            let totalPersen = (totalMurni === 0) ? 0 : (totalPengurangan / totalMurni) * 100;
            $('#rekap_bpd_total_persentase').text(totalPersen.toFixed(2) + '%');
        }
    });



    // 🔄 Fungsi untuk menghitung ulang nilai pagu pengurangan & setelah pengurangan
    function updatePaguValues() {
        $('#subkeg_table tbody tr').each(function() {
            let tr = $(this);
            let rowData = table.row(tr).data() || {};

            // Ambil nilai pagu rekening
            let paguRekening = parseInt((rowData.pagu || "0").replace(/\./g, '')) || 0;
            let persentase = parseFloat(rowData.persentase) || 0; // Persentase awal dari data

            // Hitung nilai pengurangan dan setelah pengurangan
            let paguPengurangan = Math.round((paguRekening * persentase) / 100);
            let paguSetelah = paguRekening - paguPengurangan;

            // ✅ Update tampilan slider, input, dan teks secara langsung
            tr.find('.persentase-slider').val(persentase);
            tr.find('.persentase-label').text(persentase + '%');
            tr.find('.pagu-pengurangan-input').val(formatRibuan(paguPengurangan));
            tr.find('td.col-pagu-setelah').text(formatRibuan(paguSetelah));

            // ✅ Pastikan data di DataTables diperbarui
            table.row(tr).data($.extend(rowData, {
                pagu_pengurangan: paguPengurangan,
                pagu_setelah: paguSetelah
            })).draw();
        });

        // ✅ Hitung ulang total keseluruhan setelah update
        recalcTotal();
    }


    // 🔄 Fungsi untuk menghitung ulang total seluruh pagu pengurangan & setelah pengurangan
    function recalcTotal() {
        let totalPengurangan = 0;
        let totalSetelah = 0;

        table.rows().every(function() {
            let rowData = this.data() || {};
            let paguPengurangan = parseInt((rowData.pagu_pengurangan || "0").replace(/\./g, '')) || 0;
            let paguSetelah = parseInt((rowData.pagu_setelah || "0").replace(/\./g, '')) || 0;

            totalPengurangan += paguPengurangan;
            totalSetelah += paguSetelah;
        });

        // Update total di footer
        $('#total-pagu-pengurangan').text(formatRibuan(totalPengurangan));
        $('#total-pagu-setelah').text(formatRibuan(totalSetelah));
    }


    // ============================================
    //  FUNGSI UNTUK MEMBANGUN BODY TABEL UTAMA
    // ============================================
    function buildTableMainBody() {
        // Header (9 kolom)
        let body = [[
            "Kode Sub Keg", "Nama Sub Keg", "Pagu Murni",
            "Kode Rek", "Nama Rek", "Pagu Rek",
            "Persentase", "Pagu Pengurangan", "Pagu Setelah"
        ]];

        // Loop baris Tabel Utama
        table.rows().every(function() {
            let rowData = this.data() || {};
            let tr = $(this.node());
            // Baca slider & input di DOM
            let sliderVal = tr.find('.persentase-slider').val() || '0';
            let inputVal = tr.find('.pagu-pengurangan-input').val() || '0';
            let setelahVal = tr.find('td.col-pagu-setelah').text() || '0';

            body.push([
                rowData.kode_sub_kegiatan || "",
                rowData.nama_sub_kegiatan || "",
                rowData.pagu_murni || "",
                rowData.kode_rekening || "",
                rowData.nama_rekening || "",
                rowData.pagu || "",
                sliderVal + "%",
                inputVal,
                setelahVal
            ]);
        });

        // Tambah baris footer
        let totalPaguSub = $('#total-pagu-subkeg').text() || "0";
        let totalPaguRek = $('#total-pagu-rekening').text() || "0";
        let totalPengurangan = $('#total-pagu-pengurangan').text() || "0";
        let totalSetelah = $('#total-pagu-setelah').text() || "0";

        body.push([
            { text: "Total", colSpan: 2, alignment: 'right', bold: true },
            {},
            totalPaguSub,
            { text: "Total Rek", colSpan: 2, alignment: 'right', bold: true },
            {},
            totalPengurangan,
            totalSetelah
        ]);

        return body;
    }

    // ============================================
    //  FUNGSI UNTUK MEMBANGUN BODY TABEL REKAP
    // ============================================
    function buildTableRekapBody() {
        // Header (7 kolom)
        let body = [[
            "Kode Rekening", "Nama Rekening", "Nama OPD",
            "Pagu Murni", "Pagu Pengurangan", "Pagu Setelah", "Persentase"
        ]];

        // Ambil data dari rekapTable
        let data = rekapTable.rows().data().toArray();
        data.forEach(function(item) {
            body.push([
                item.kode_rekening || "",
                item.nama_rekening || "",
                item.nama_opd || "",
                String(item.pagu_murni || ""),
                String(item.pagu_pengurangan || ""),
                String(item.pagu_setelah || ""),
                item.persentase || ""
            ]);
        });

        // Tambah baris footer
        let totalMurni = $('#rekap_bpd_total').text() || '0';
        let totalPengurangan = $('#rekap_bpd_total_pengurangan').text() || '0';
        let totalSetelah = $('#rekap_bpd_total_setelah').text() || '0';
        let totalPersen = $('#rekap_bpd_total_persentase').text() || '0%';

        body.push([
            { text: "Total", colSpan: 3, alignment: 'right', bold: true },
            {},
            {},
            totalMurni,
            totalPengurangan,
            totalSetelah,
            totalPersen
        ]);

        return body;
    }

    // Format ribuan
    function formatRibuan(raw) {
        let numeric = raw.replace(/\D/g, '');
        let val = parseInt(numeric) || 0;
        return new Intl.NumberFormat('id-ID').format(val);
    }

    // Recalc kolom 8 & 9 => total
    function recalcPenguranganSetelah() {
        let totalPengurangan = 0;
        let totalSetelah = 0;

        table.rows().every(function() {
            let rowNode = this.node();
            let col8 = $(rowNode).find('.pagu-pengurangan-input').val() || '0';
            let col9 = $(rowNode).find('td.col-pagu-setelah').text() || '0';

            let valPengurangan = parseInt(col8.replace(/\./g, '')) || 0;
            let valSetelah = parseInt(col9.replace(/\./g, '')) || 0;

            totalPengurangan += valPengurangan;
            totalSetelah += valSetelah;
        });

        $('#total-pagu-pengurangan').text(formatRibuan(String(totalPengurangan)));
        $('#total-pagu-setelah').text(formatRibuan(String(totalSetelah)));
    }

    // (C) Isi Tabel Rekap
    function rekapBpdLocal() {
        let opdText = $("#kode_opd option:selected").text() || "";
        let splitted = opdText.split("-");
        splitted.shift(); 
        let namaOpd = splitted.join("-").trim();

        // Buat map rekap
        let rekapMap = {};

        table.rows().every(function() {
            let rowData = this.data() || {};
            if (rowData.nama_rekening && rowData.nama_rekening.toLowerCase().includes('belanja perjalanan dinas')) {
                let codeRek = rowData.kode_rekening;
                let nameRek = rowData.nama_rekening;

                let paguStr = rowData.pagu || '0';
                let paguMurni = parseInt(paguStr.replace(/\./g, '')) || 0;

                let rowNode = this.node();
                let col8Val = $(rowNode).find('.pagu-pengurangan-input').val() || '0';
                let penguranganVal = parseInt(col8Val.replace(/\./g, '')) || 0;

                let setelahVal = paguMurni - penguranganVal;

                if (!rekapMap[codeRek]) {
                    rekapMap[codeRek] = {
                        kode_rekening: codeRek,
                        nama_rekening: nameRek,
                        nama_opd: namaOpd,
                        pagu_murni: 0,
                        pagu_pengurangan: 0,
                        pagu_setelah: 0
                    };
                }
                rekapMap[codeRek].pagu_murni += paguMurni;
                rekapMap[codeRek].pagu_pengurangan += penguranganVal;
                rekapMap[codeRek].pagu_setelah += setelahVal;
            }
        });

        // Konversi rekapMap ke array
        let result = [];
        for (let codeRek in rekapMap) {
            let item = rekapMap[codeRek];
            let persen = (item.pagu_murni === 0) ? 0 : (item.pagu_pengurangan / item.pagu_murni) * 100;
            result.push({
                kode_rekening: codeRek,
                nama_rekening: item.nama_rekening,
                nama_opd: item.nama_opd,
                pagu_murni: item.pagu_murni,
                pagu_pengurangan: item.pagu_pengurangan,
                pagu_setelah: item.pagu_setelah,
                persentase: persen.toFixed(2) + '%'
            });
        }

        // Isi rekapTable
        rekapTable.clear().rows.add(result).draw();
    }

    // (D) Event Filter
  // 🔄 Event Filter OPD
    $('#filter-btn').on('click', function() {
        let kodeOpd = $('#kode_opd').val();
        if (kodeOpd) {
            $.ajax({
                url: "{{ route('simulasi.get-subkeg-by-opd') }}",
                type: "GET",
                data: { kode_opd: kodeOpd },
                dataType: "json",
                success: function(response) {
                    // Update tabel dengan data yang difilter
                    table.clear().rows.add(response).draw();

                    // 🔥 Hitung ulang nilai Pagu Pengurangan & Pagu Setelah Pengurangan
                    updatePaguValues();
                },
                error: function(xhr, status, error) {
                    console.error("Error mengambil data subkegiatan:", error);
                }
            });
        } else {
            table.clear().draw();
            $('#total-pagu-pengurangan').text("0");
            $('#total-pagu-setelah').text("0");
        }
    });

    // 🔄 Panggil fungsi update saat halaman pertama kali dimuat
    table.on('draw', function() {
        updatePaguValues();
    });

    // 🔄 Format angka dengan ribuan
    function formatRibuan(raw) {
        let numeric = raw.replace(/\D/g, '');
        let val = parseInt(numeric) || 0;
        return new Intl.NumberFormat('id-ID').format(val);
    }




    // (E) Reset Filter
    $('#reset-filter').on('click', function() {
        $('#kode_opd').val("");
        table.clear().draw();
        rekapTable.clear().draw();

        $("#rekapBpdTitle").text("Rekap Perjalanan Dinas");
        $('#rekap_bpd_total').text("0");
        $('#rekap_bpd_total_pengurangan').text("0");
        $('#rekap_bpd_total_setelah').text("0");
        $('#rekap_bpd_total_persentase').text("0%");
    });

    // (F) Slider => update kolom 8 & 9
    $('#subkeg_table tbody').on('input', '.persentase-slider', function() {
        let val = parseFloat($(this).val()) || 0;
        $(this).closest('div').find('.persentase-label').text(val + '%');

        let tr = $(this).closest('tr');
        let rowData = table.row(tr).data() || {};
        let paguStr = rowData.pagu || '0';
        let pagu = parseInt(paguStr.replace(/\./g, '')) || 0;

        let pengurangan = Math.round(pagu * val / 100);
        let setelah = pagu - pengurangan;

        tr.find('.pagu-pengurangan-input').val(formatRibuan(String(pengurangan)));
        tr.find('td.col-pagu-setelah').text(formatRibuan(String(setelah)));

        recalcPenguranganSetelah();
        rekapBpdLocal();
    });

    // (G) Input manual kolom 8 => update slider & col9
    $('#subkeg_table tbody').on('input', '.pagu-pengurangan-input', function() {
        let raw = $(this).val() || '0';
        let fm = formatRibuan(raw);
        $(this).val(fm);

        let valPengurangan = parseInt(fm.replace(/\./g, '')) || 0;

        let tr = $(this).closest('tr');
        let rowData = table.row(tr).data() || {};
        let paguStr = rowData.pagu || '0';
        let pagu = parseInt(paguStr.replace(/\./g, '')) || 0;

        if (valPengurangan > pagu) {
            valPengurangan = pagu;
            $(this).val(formatRibuan(String(pagu)));
        }

        let persen = (pagu === 0) ? 0 : (valPengurangan / pagu) * 100;
        if (persen > 100) persen = 100;

        let slider = tr.find('.persentase-slider');
        let label = slider.closest('div').find('.persentase-label');
        slider.val(persen.toFixed(0));
        label.text(persen.toFixed(0) + '%');

        let setelah = pagu - valPengurangan;
        tr.find('td.col-pagu-setelah').text(formatRibuan(String(setelah)));

        recalcPenguranganSetelah();
        rekapBpdLocal();
    });

    // (H) Tombol Konfirmasi => Simpan ke DB
    $('#btn-konfirmasi').on('click', function() {
    let kodeOpd = $('#kode_opd').val();
    if (!kodeOpd) {
        alert("Silakan pilih OPD terlebih dahulu!");
        return;
    }

    let changes = [];

    $('#subkeg_table tbody tr').each(function() {
        let row = $(this);
        let kodeSubKeg = row.find('td:eq(0)').text().trim(); // Kode Sub Kegiatan
        let kodeRekening = row.find('td:eq(3)').text().trim(); // Kode Rekening
        let persenVal = row.find('.persentase-slider').val() || "0"; // Nilai Persentase dari Slider
        
        if (kodeSubKeg && kodeRekening) {
            changes.push({
                kode_opd: kodeOpd,
                kode_sub_kegiatan: kodeSubKeg,
                kode_rekening: kodeRekening,
                persentase: parseFloat(persenVal) // Pastikan nilai float
            });
        }
    });

    if (changes.length === 0) {
        alert("Tidak ada data perubahan untuk disimpan.");
        return;
    }

    console.log("Data yang dikirim:", changes); // Debugging untuk cek data sebelum dikirim

    // Kirim data ke server
    $.ajax({
        url: "{{ route('simulasi.updatepersentasesubkeg') }}",
        type: "POST",
        contentType: "application/json",
        data: JSON.stringify({
            _token: "{{ csrf_token() }}",
            data: changes
        }),
        success: function(resp) {
            console.log("Respon server:", resp);
            alert("Perubahan berhasil disimpan!");
        },
        error: function(xhr, status, error) {
            console.error("Error:", xhr.responseText);
            alert("Gagal menyimpan perubahan.");
        }
    });
});



    // ============== FUNGSI BANGUN TABEL UTAMA & REKAP =============

    // Membangun body Tabel Utama
    function buildTableMainBody() {
        // Header 9 kolom
        let body = [[
            "Kode Sub Keg", "Nama Sub Keg", "Pagu Murni",
            "Kode Rek", "Nama Rek", "Pagu Rek",
            "Persentase", "Pagu Pengurangan", "Pagu Setelah"
        ]];

        // Loop baris Tabel Utama
        table.rows().every(function() {
            let rowData = this.data() || {};
            let tr = $(this.node());
            let sliderVal = tr.find('.persentase-slider').val() || '0';
            let inputVal = tr.find('.pagu-pengurangan-input').val() || '0';
            let setelahVal = tr.find('td.col-pagu-setelah').text() || '0';

            body.push([
                rowData.kode_sub_kegiatan || "",
                rowData.nama_sub_kegiatan || "",
                rowData.pagu_murni || "",
                rowData.kode_rekening || "",
                rowData.nama_rekening || "",
                rowData.pagu || "",
                sliderVal + "%",
                inputVal,
                setelahVal
            ]);
        });

        // Tambah baris footer
        let totalPaguSub = $('#total-pagu-subkeg').text() || "0";
        let totalPaguRek = $('#total-pagu-rekening').text() || "0";
        let totalPengurangan = $('#total-pagu-pengurangan').text() || "0";
        let totalSetelah = $('#total-pagu-setelah').text() || "0";

        body.push([
            { text: "Total", colSpan: 2, alignment: 'right', bold: true },
            {},
            totalPaguSub,
            { text: "Total Rek", colSpan: 2, alignment: 'right', bold: true },
            {},
            totalPengurangan,
            totalSetelah
        ]);

        return body;
    }

    // Membangun body Tabel Rekap
    function buildTableRekapBody() {
        // Header 7 kolom
        let body = [[
            "Kode Rekening", "Nama Rekening", "Nama OPD",
            "Pagu Murni", "Pagu Pengurangan", "Pagu Setelah", "Persentase"
        ]];

        // Ambil data rekapTable
        let data = rekapTable.rows().data().toArray();
        data.forEach(function(item) {
            body.push([
                item.kode_rekening || "",
                item.nama_rekening || "",
                item.nama_opd || "",
                String(item.pagu_murni || ""),
                String(item.pagu_pengurangan || ""),
                String(item.pagu_setelah || ""),
                item.persentase || ""
            ]);
        });

        // Tambah baris footer
        let totalMurni = $('#rekap_bpd_total').text() || '0';
        let totalPengurangan = $('#rekap_bpd_total_pengurangan').text() || '0';
        let totalSetelah = $('#rekap_bpd_total_setelah').text() || '0';
        let totalPersen = $('#rekap_bpd_total_persentase').text() || '0%';

        body.push([
            { text: "Total", colSpan: 3, alignment: 'right', bold: true },
            {},
            {},
            totalMurni,
            totalPengurangan,
            totalSetelah,
            totalPersen
        ]);

        return body;
    }

    

});
</script>
@endsection
