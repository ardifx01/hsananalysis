@extends('layouts.app')

@section('title', 'Simulasi Penyesuaian Per OPD')
@section('page-title', 'Simulasi Penyesuaian Per OPD')

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
        .input-small {
            width: 80px;
            text-align: center;
        }

        .table-sm th,
        .table-sm td {
            padding: 6px 10px;
            font-size: 12px;
        }

        .btn-container {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 10px;
        }

        .alert-info {
            font-size: 14px;
            text-align: center;
            padding: 10px;
        }

        .table-sm th,
        .table-sm td {
            padding: 6px 10px;
            font-size: 12px;
            white-space: nowrap;
            /* Mencegah teks memanjang keluar */
        }

        td.nama-rekening {
            max-width: 250px;
            /* Tentukan ukuran tetap */

            white-space: normal;
            /* Memungkinkan wrap text */

        }
    </style>

    <div class="container">

        <!-- Form Filter -->
        <form id="filter-form" class="row g-3 mb-3">
            <div class="col-md-4">
                <label for="kode_opd" class="form-label">Pilih OPD</label>
                <select name="kode_opd" id="kode_opd" class="form-select">
                    <option value="">Silakan pilih OPD</option>
                    @foreach ($opds as $opd)
                        <option value="{{ $opd->kode_skpd }}"
                            {{ request('kode_opd') == $opd->kode_skpd ? 'selected' : '' }}>
                            {{ $opd->kode_skpd }} - {{ $opd->nama_skpd }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-4 d-flex align-items-end">
                <button type="submit" class="btn btn-primary w-100">Filter</button>
                <button type="reset" id="reset-filter" class="btn btn-secondary w-100 ms-2">Reset</button>
            </div>
        </form>

        @if (request('kode_opd'))
            <!-- Button Container -->
            <div class="btn-container">
                <div>
                    <button type="submit" form="update-form" class="btn btn-success">Simpan Perubahan</button>
                    <button id="reset-nilai" class="btn btn-warning">Reset Nilai OPD Ini</button>
                </div>
            </div>

            <!-- Form Update Persentase -->
            <form id="update-form" action="{{ route('simulasi.set-opd-rek.update') }}" method="POST">
                @csrf
                <input type="hidden" name="kode_opd" value="{{ request('kode_opd') }}">

                <div class="table-responsive">
                    <table id="rekapTable" class="table table-striped table-bordered table-sm">
                        <thead class="table-dark">
                            <tr>
                                <th>No</th>
                                <th>Kode Rekening</th>
                                <th>Nama Rekening</th>
                                <th>Pagu Original</th>
                                <th>Persentase Penyesuaian</th>
                                <th>Jumlah Penyesuaian</th>
                                <th>Pagu Setelah Penyesuaian</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($data as $index => $row)
                                @php
                                    $nilai_penyesuaian = ($row->pagu_original * $row->persentase_penyesuaian) / 100;
                                    $pagu_setelah = $row->pagu_original - $nilai_penyesuaian;
                                @endphp
                                <tr>
                                    <td>{{ $index + 1 }}</td>
                                    <td>
                                        <input type="hidden" name="kode_rekening[]" value="{{ $row->kode_rekening }}">
                                        <span>{{ $row->kode_rekening }}</span>
                                    </td>
                                    <td class="nama-rekening">{{ $row->nama_rekening }}</td>
                                    <td class="text-end pagu-original" data-value="{{ $row->pagu_original }}">
                                        {{ number_format($row->pagu_original, 0, ',', '.') }}
                                    </td>
                                    <td data-export="{{ $row->persentase_penyesuaian }}">
                                        <input type="number" class="form-control persentase-penyesuaian"
                                            name="persentase_penyesuaian[]" value="{{ $row->persentase_penyesuaian }}"
                                            min="0" max="100" step="0.01">
                                    </td>

                                    <td class="text-end nilai-penyesuaian" data-export="{{ $nilai_penyesuaian }}">
                                        {{ number_format($nilai_penyesuaian, 0, ',', '.') }}
                                    </td>

                                    <td class="text-end pagu-setelah" data-export="{{ $pagu_setelah }}">
                                        {{ number_format($pagu_setelah, 0, ',', '.') }}
                                    </td>



                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot class="table-dark">
                            <tr>
                                <th colspan="3" class="text-end">Total:</th>
                                <th id="total-pagu-original" class="text-end">0</th>
                                <th id="total-persentase" class="text-end">0%</th>
                                <th id="total-nilai-penyesuaian" class="text-end">0</th>
                                <th id="total-pagu-setelah" class="text-end">0</th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </form>
        @else
            <div class="alert alert-info">Silakan pilih OPD untuk menampilkan data.</div>
        @endif
    </div>

    <!-- jQuery untuk DataTables & Perhitungan -->
    <script>
        $(document).ready(function() {
            function formatNumber(value) {
                return new Intl.NumberFormat('id-ID').format(value);
            }

            function removeThousandSeparator(value) {
                return value.replace(/\./g, ''); // Hapus titik pemisah ribuan
            }

            function formatPersentase(value) {
                return value.toString().replace('.', ','); // Pastikan koma sebagai desimal
            }

            function hitungTotal() {
                let totalPaguOriginal = 0;
                let totalNilaiPenyesuaian = 0;
                let totalPaguSetelah = 0;
                let totalPersentase = 0; // Tambahkan total persentase

                let jumlahBaris = 0; // Untuk menghitung rata-rata persentase

                $('tbody tr').each(function() {
                    let row = $(this);
                    let paguOriginal = parseFloat(removeThousandSeparator(row.find('.pagu-original')
                        .text())) || 0;
                    let persentase = parseFloat(row.find('.persentase-penyesuaian').val().replace(',',
                        '.')) || 0;

                    if (!isNaN(persentase) && paguOriginal > 0) {
                        jumlahBaris++;
                    } else {
                        persentase = 0; // Pastikan nilai tidak NaN
                    }

                    let nilaiPenyesuaian = (paguOriginal * persentase) / 100;
                    let paguSetelah = paguOriginal - nilaiPenyesuaian;

                    row.find('.nilai-penyesuaian').text(formatNumber(nilaiPenyesuaian));
                    row.find('.pagu-setelah').text(formatNumber(paguSetelah));

                    totalPaguOriginal += paguOriginal;
                    totalNilaiPenyesuaian += nilaiPenyesuaian;
                    totalPaguSetelah += paguSetelah;
                    totalPersentase += persentase;
                });

                $('#total-pagu-original').text(formatNumber(totalPaguOriginal));
                $('#total-nilai-penyesuaian').text(formatNumber(totalNilaiPenyesuaian));
                $('#total-pagu-setelah').text(formatNumber(totalPaguSetelah));

                // Hitung rata-rata persentase, hindari NaN jika jumlahBaris = 0
                let avgPersentase = jumlahBaris > 0 ? formatPersentase((totalPersentase / jumlahBaris).toFixed(2)) :
                    "0,00";
                $('#total-persentase').text(avgPersentase + "%");

                // Simpan nilai total untuk ekspor
                $('#rekapTable').attr('data-total-pagu-original', totalPaguOriginal);
                $('#rekapTable').attr('data-total-nilai-penyesuaian', totalNilaiPenyesuaian);
                $('#rekapTable').attr('data-total-pagu-setelah', totalPaguSetelah);
                $('#rekapTable').attr('data-total-persentase', avgPersentase);
            }

            $('.persentase-penyesuaian').on('input', function() {
                hitungTotal();
            });

            hitungTotal();

            /** ✅ Tambahkan DataTables dengan fitur Export */
            $('#rekapTable').DataTable({
                dom: 'Bfrtip',
                buttons: [{
                        extend: 'copy',
                        text: 'Copy'
                    },
                    {
                        extend: 'csv',
                        text: 'CSV'
                    },
                    {
                        extend: 'excelHtml5',
                        text: 'Export Excel',
                        footer: true,
                        exportOptions: {
                            columns: ':visible',
                            format: {
                                body: function(data, row, column, node) {
                                    if (column === 3) {
                                        let nilai = $(node).attr('data-value');
                                        return nilai ? nilai :
                                            "0";
                                    }
                                    // Jika kolom persentase, ambil dari data-export
                                    if (column === 4) {
                                        let persen = $(node).attr('data-export');
                                        return persen ? persen : "0,00";
                                    }
                                    if (column ===
                                        5) { // Pastikan ini sesuai dengan indeks kolom di tabel
                                        let nilai = $(node).attr('data-export');
                                        return nilai ? nilai :
                                            "0"; // Ambil nilai asli tanpa titik pemisah ribuan
                                    }

                                    if (column ===
                                        6) { // Pastikan ini sesuai dengan indeks kolom di tabel
                                        let nilai = $(node).attr('data-export');
                                        return nilai ? nilai :
                                            "0"; // Ambil nilai asli tanpa titik pemisah ribuan
                                    }
                                    return data.replace(/<[^>]*>?/gm, '');
                                },
                                footer: function(data, row, column, node) {
                                    let totalPaguOriginal = removeThousandSeparator($('#rekapTable')
                                        .attr('data-total-pagu-original') || '0');
                                    let totalNilaiPenyesuaian = removeThousandSeparator($(
                                        '#rekapTable').attr(
                                        'data-total-nilai-penyesuaian') || '0');
                                    let totalPaguSetelah = removeThousandSeparator($('#rekapTable')
                                        .attr('data-total-pagu-setelah') || '0');
                                    let totalPersentase = $('#rekapTable').attr(
                                        'data-total-persentase') || "0,00";

                                    if (column === 3) return totalPaguOriginal;
                                    if (column === 4) return totalPersentase + "%";
                                    if (column === 5) return totalNilaiPenyesuaian;
                                    if (column === 6) return totalPaguSetelah;

                                    return data;
                                }
                            }
                        }
                    },
                    {
                        extend: 'pdfHtml5',
                        text: 'Export PDF',
                        orientation: 'landscape'
                    },
                    {
                        extend: 'print',
                        text: 'Print'
                    }
                ],
                paging: false,
                searching: true,
                responsive: true
            });

        });
    </script>

@endsection
