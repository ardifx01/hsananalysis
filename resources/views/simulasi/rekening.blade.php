@extends('layouts.app')

@section('title', 'Rekap Penyesuaian % Rekening')
@section('page-title', 'Rekap Penyesuaian % Rekening')

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
        .table-sm th,
        .table-sm td {
            padding: 6px 10px;
            font-size: 12px;
        }

        .btn-container {
            margin-bottom: 10px;
        }




        td.nama-rekening {
            max-width: 250px;
            /* Tentukan ukuran tetap */

            white-space: normal;
            /* Memungkinkan wrap text */

        }
    </style>

    <div class="container">
    <div class="card">
        <div class="card-header">
            
        </div>
        <div class="card-body">
        <div class="table-responsive">
            <table id="rekapTable" class="table table-striped table-bordered table-sm">
                <thead class="table-dark">
                    <tr>
                        <th>No</th>
                        <th>Kode Rekening</th>
                        <th>Nama Rekening</th>
                        <th>Pagu Murni</th>
                        <th>Persentase Pengurangan</th>
                        <th>Pagu Pengurangan</th>
                        <th>Pagu Setelah Pengurangan</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $total_pagu_original = 0;
                        $total_nilai_penyesuaian = 0;
                        $total_pagu_setelah = 0;
                    @endphp
                    @foreach ($data as $index => $row)
                        @php
                            $total_pagu_original += $row->pagu_original;
                            $total_nilai_penyesuaian += $row->nilai_penyesuaian_total;
                            $total_pagu_setelah += $row->pagu_setelah_penyesuaian;
                        @endphp
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>{{ $row->kode_rekening }}</td>
                            <td class="nama-rekening">{{ $row->nama_rekening }}</td>
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
        </div>
    </div>

    <!-- jQuery untuk DataTables -->
    <script>
        $(document).ready(function() {


            function hitungTotalPersentase() {
                let totalPaguMurni = $("#rekapTable tfoot th:contains('Pagu Murni')").next().text().trim().replace(
                    /\./g, '').replace(',', '.');
                let totalPaguPenyesuaian = $("#rekapTable tfoot th:contains('Pagu Penyesuaian')").next().text()
                    .trim().replace(/\./g, '').replace(',', '.');
                let totalPaguSetelah = $("#rekapTable tfoot th:contains('Pagu Setelah Pengurangan')").next().text()
                    .trim().replace(/\./g, '').replace(',', '.');

                totalPaguMurni = parseFloat(totalPaguMurni) || 0;
                totalPaguPenyesuaian = parseFloat(totalPaguPenyesuaian) || 0;
                totalPaguSetelah = parseFloat(totalPaguSetelah) || 0;

                let totalPersentase = totalPaguMurni > 0 ? (totalPaguPenyesuaian / totalPaguMurni * 100).toFixed(
                    2) : "0.00";
                totalPersentase = totalPersentase.replace('.', ',');

                $('#total-persentase').text(totalPersentase + "%");
            }

            // Panggil fungsi setelah halaman dimuat
            hitungTotalPersentase();



            let table = $('#rekapTable').DataTable({
                dom: 'Bfrtip',
                buttons: [{
                        extend: 'excelHtml5',
                        text: 'Export Excel',
                        className: 'btn btn-success',
                        exportOptions: {
                            columns: ':visible',
                            format: {
                                body: function(data, row, column, node) {
                                    // Hilangkan pemisah ribuan (.) sebelum diekspor
                                    return data.replace(/\./g, '').replace(/,/g, '.');
                                }
                            }
                        }
                    },
                    {
                        extend: 'pdfHtml5',
                        text: 'Export PDF',
                        className: 'btn btn-danger',
                        orientation: 'landscape',
                        pageSize: 'A4',
                        title: function() {
                            return "Rekap Penyesuaian Rekening Belanja";
                        },
                        customize: function(doc) {
                            let tableContent = doc.content.find(item => item.table);
                            if (!tableContent) return;

                            // 🔥 Ubah ukuran font agar lebih proporsional
                            doc.defaultStyle.fontSize = 10;
                            doc.styles.tableHeader.fontSize = 12;
                            doc.styles.title.fontSize = 14;

                            // 🔥 Tambahkan timestamp tanggal & waktu saat export
                            let currentDateTime = new Date().toLocaleString("id-ID", {
                                day: '2-digit',
                                month: '2-digit',
                                year: 'numeric',
                                hour: '2-digit',
                                minute: '2-digit',
                                second: '2-digit'
                            });

                            doc.content.splice(1, 0, {
                                text: `Tanggal Export: ${currentDateTime}`,
                                margin: [0, 0, 0, 10],
                                alignment: 'right',
                                fontSize: 10
                            });

                            // 🔥 Ubah header tabel agar sesuai dengan permintaan
                            tableContent.table.body[0] = [{
                                    text: "No",
                                    bold: true,
                                    alignment: "center"
                                },
                                {
                                    text: "Kode Rekening",
                                    bold: true,
                                    alignment: "center"
                                },
                                {
                                    text: "Nama Rekening",
                                    bold: true,
                                    alignment: "center"
                                },
                                {
                                    text: "Pagu Murni",
                                    bold: true,
                                    alignment: "right"
                                }, // 🔄 Sebelumnya: "Pagu Original"
                                {
                                    text: "Persentase Pengurangan",
                                    bold: true,
                                    alignment: "right"
                                },
                                {
                                    text: "Pagu Pengurangan",
                                    bold: true,
                                    alignment: "right"
                                }, // 🔄 Sebelumnya: "Nilai Penyesuaian"
                                {
                                    text: "Pagu Setelah Pengurangan",
                                    bold: true,
                                    alignment: "right"
                                }
                            ];

                            // 🔥 Format angka dan pastikan kode rekening tampil sesuai database
                            tableContent.table.body.forEach(function(row, index) {
                                if (index > 0) {
                                    // Format Kode Rekening sesuai format database (pastikan ada titik pemisah)
                                    let kodeRekening = row[1].text.trim();
                                    kodeRekening = kodeRekening.replace(
                                        /(\d{2})(?=\d{2,})/g, '$1.');
                                    row[1].text = kodeRekening;

                                    // Format angka menjadi format dengan titik ribuan
                                    row[3].text = new Intl.NumberFormat('id-ID').format(
                                        parseInt(row[3].text)); // Pagu Murni
                                    row[4].text = row[4].text +
                                    " %"; // Persentase tetap dalam format %
                                    row[5].text = new Intl.NumberFormat('id-ID').format(
                                        parseInt(row[5].text)); // Pagu Penyesuaian
                                    row[6].text = new Intl.NumberFormat('id-ID').format(
                                        parseInt(row[6].text)); // Pagu Setelah Pengurangan

                                    // Rata kanan angka pada semua kolom pagu
                                    row[3].alignment = "right";
                                    row[4].alignment = "right";
                                    row[5].alignment = "right";
                                    row[6].alignment = "right";
                                }
                            });

                            // 🔥 Ambil total dari elemen footer di tabel HTML
                            let totalPaguMurni = $("#rekapTable tfoot th:eq(1)").text().trim()
                                .replace(/\./g, '').replace(',', '.');
                            let totalPersentase = $("#rekapTable tfoot th:eq(2)").text().trim();
                            let totalPaguPenyesuaian = $("#rekapTable tfoot th:eq(3)").text().trim()
                                .replace(/\./g, '').replace(',', '.');
                            let totalPaguSetelah = $("#rekapTable tfoot th:eq(4)").text().trim()
                                .replace(/\./g, '').replace(',', '.');

                            totalPaguMurni = parseFloat(totalPaguMurni) || 0;
                            totalPaguPenyesuaian = parseFloat(totalPaguPenyesuaian) || 0;
                            totalPaguSetelah = parseFloat(totalPaguSetelah) || 0;

                            let totalPersentaseFinal = totalPaguMurni > 0 ? (totalPaguPenyesuaian /
                                totalPaguMurni * 100).toFixed(2) : "0.00";
                            totalPersentaseFinal = totalPersentaseFinal.replace('.', ',');

                            // 🔥 Tambahkan baris total ke dalam PDF
                            tableContent.table.body.push([{
                                    text: "TOTAL",
                                    bold: true,
                                    alignment: "right",
                                    colSpan: 3
                                },
                                {}, {},
                                {
                                    text: new Intl.NumberFormat('id-ID').format(
                                        totalPaguMurni),
                                    bold: true,
                                    alignment: "right"
                                }, // Total Pagu Murni
                                {
                                    text: totalPersentaseFinal + " %",
                                    bold: true,
                                    alignment: "right"
                                }, // Total Persentase
                                {
                                    text: new Intl.NumberFormat('id-ID').format(
                                        totalPaguPenyesuaian),
                                    bold: true,
                                    alignment: "right"
                                }, // Total Pagu Penyesuaian
                                {
                                    text: new Intl.NumberFormat('id-ID').format(
                                        totalPaguSetelah),
                                    bold: true,
                                    alignment: "right"
                                } // Total Pagu Setelah Pengurangan
                            ]);

                            // 🔥 Perbaiki tata letak agar lebih rapi
                            let objLayout = {};
                            objLayout['hLineWidth'] = function(i) {
                                return 0.8;
                            };
                            objLayout['vLineWidth'] = function(i) {
                                return 0.8;
                            };
                            tableContent.layout = objLayout;
                        },
                        exportOptions: {
                            columns: [0, 1, 2, 3, 4, 5,
                            6], // 🔥 Pastikan indeks sesuai dengan tabel setelah perubahan
                            format: {
                                body: function(data, row, column, node) {
                                    return data.replace(/\./g, '').replace(/,/g,
                                    '.'); // 🔄 Hapus pemisah ribuan sebelum export
                                }
                            }
                        }
                    },
                    {
                        extend: 'print',
                        text: 'Print',
                        className: 'btn btn-primary'
                    }
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
